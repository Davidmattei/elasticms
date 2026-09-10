#!/usr/bin/env php
<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\SingleCommandApplication;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Translation\Util\ArrayConverter;
use Symfony\Component\Yaml\Yaml;

const ROOT = __DIR__.'/..';
const LOCALE = 'en';
const SOURCE_DOMAIN = 'EMSCoreBundle';
const TARGET_DOMAIN = 'emsco-core+intl-icu';
const SOURCE_DIR = ROOT.'/EMS/core-bundle/translations';
const TARGET_DIR = ROOT.'/EMS/core-bundle/translations';
const SEARCH_PATHS = [
    ROOT.'/EMS/admin-ui-bundle/templates',
    ROOT.'/EMS/core-bundle/src',
    ROOT.'/EMS/core-bundle/templates',
];

/**
 * @param array<string, mixed> $tree
 *
 * @return array<string, string>
 */
function flatten(array $tree, string $prefix = ''): array
{
    $flat = [];
    foreach ($tree as $key => $value) {
        $path = '' === $prefix ? (string) $key : $prefix.'.'.$key;
        if (\is_array($value)) {
            $flat += flatten($value, $path);
        } else {
            $flat[$path] = (string) $value;
        }
    }

    return $flat;
}

/**
 * @return array<string, string>
 */
function loadYaml(string $file): array
{
    if (!\is_file($file)) {
        return [];
    }

    return flatten((array) Yaml::parseFile($file));
}

/**
 * @param array<string, string> $messages
 */
function dumpYaml(string $file, array $messages): void
{
    \ksort($messages);

    \file_put_contents($file, Yaml::dump(
        input: ArrayConverter::expandToTree($messages),
        inline: 5,
        flags: Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK
    ));
}

function removeYamlKey(string $file, string $key): bool
{
    $lines = \file($file, \FILE_IGNORE_NEW_LINES);

    if (false === $lines) {
        return false;
    }

    $stack = [];
    $start = null;
    $indent = 0;

    foreach ($lines as $i => $line) {
        if (!\preg_match('/^(\s*)(?:\'([^\']+)\'|"([^"]+)"|([^\s:#][^:]*)):(\s|$)/', $line, $match)) {
            continue;
        }

        $lineIndent = \strlen($match[1]);
        $name = $match[2] ?: ($match[3] ?: $match[4]);

        while ([] !== $stack && $stack[\array_key_last($stack)][0] >= $lineIndent) {
            \array_pop($stack);
        }
        $stack[] = [$lineIndent, $name];

        if ($key === \implode('.', \array_column($stack, 1))) {
            $start = $i;
            $indent = $lineIndent;
            break;
        }
    }

    if (null === $start) {
        return false;
    }

    $end = $start;
    for ($i = $start + 1, $count = \count($lines); $i < $count; ++$i) {
        $line = $lines[$i];
        if ('' !== \trim($line) && \strlen($line) - \strlen(\ltrim($line)) <= $indent) {
            break;
        }
        $end = $i;
    }

    \array_splice($lines, $start, $end - $start + 1);

    do {
        $removed = false;
        foreach ($lines as $i => $line) {
            if (!\preg_match('/^(\s*)\S[^:]*:\s*$/', $line, $match)) {
                continue;
            }

            $next = $lines[$i + 1] ?? null;
            if (null === $next || \strlen($next) - \strlen(\ltrim($next)) <= \strlen($match[1])) {
                \array_splice($lines, $i, 1);
                $removed = true;
                break;
            }
        }
    } while ($removed);

    \file_put_contents($file, \implode("\n", $lines)."\n");

    return true;
}

/**
 * @return array{message: string, warnings: list<string>}
 */
function toIcu(string $message): array
{
    $warnings = [];

    if (\preg_match('/\|/', $message) || \preg_match('/^\s*[{\[]\d/', $message)) {
        $warnings[] = 'legacy pluralization detected, needs manual {count, plural, ...}';
    }

    $icu = \preg_replace_callback(
        '/%([a-zA-Z_][a-zA-Z0-9_]*)%/',
        static fn (array $m) => '{'.$m[1].'}',
        $message
    ) ?? $message;

    if (\preg_match('/%[^%\s]*%/', $icu)) {
        $warnings[] = 'unconverted placeholder remaining';
    }

    return ['message' => $icu, 'warnings' => $warnings];
}

$command = static function (InputInterface $input, OutputInterface $output): int {
    $io = new SymfonyStyle($input, $output);

    $oldKey = (string) $input->getArgument('old-key');
    $newKey = (string) $input->getArgument('new-key');
    $newMessage = $input->getArgument('message');

    $io->title(\sprintf('Migrate %s -> %s', $oldKey, $newKey));

    $sourceFile = \sprintf('%s/%s.%s.yml', SOURCE_DIR, SOURCE_DOMAIN, LOCALE);
    $targetFile = \sprintf('%s/%s.%s.yml', TARGET_DIR, TARGET_DOMAIN, LOCALE);

    $source = loadYaml($sourceFile);
    $target = loadYaml($targetFile);

    if (!isset($source[$oldKey])) {
        $io->error(\sprintf('Key not found in %s', \basename($sourceFile)));

        return Command::FAILURE;
    }

    $io->section('Code');

    $finder = new Finder()
        ->files()
        ->ignoreUnreadableDirs()
        ->in(\array_filter(SEARCH_PATHS, \is_dir(...)))
        ->name(['*.php', '*.twig'])
        ->contains($oldKey);

    $pattern = \sprintf('/([\'"])%s\1/', \preg_quote($oldKey, '/'));

    $files = 0;
    foreach ($finder as $file) {
        $updated = \preg_replace_callback(
            $pattern,
            static fn (array $m) => $m[1].$newKey.$m[1],
            $file->getContents(),
            -1,
            $replacements
        );

        if (null === $updated || 0 === $replacements) {
            $io->writeln(\sprintf('<comment>%s (no quoted match)</comment>', $file->getRelativePathname()));
            continue;
        }

        ++$files;
        $io->writeln(\sprintf('%s (%d)', $file->getRelativePathname(), $replacements));
        \file_put_contents($file->getRealPath(), $updated);
    }

    $io->section('Translations');

    $hasExplicitMessage = \is_string($newMessage) && '' !== $newMessage;

    if (0 === $files) {
        $io->warning(\sprintf('%s is not used in the code, removing it without migrating', $oldKey));
    } elseif (isset($target[$newKey]) && !$hasExplicitMessage) {
        $io->warning(\sprintf('%s already exists, reusing it as-is: %s', $newKey, $target[$newKey]));
    } elseif (isset($target[$newKey]) && $target[$newKey] === $newMessage) {
        $io->writeln(\sprintf('<info>%s</info> already up to date', $newKey));
    } else {
        $icu = $hasExplicitMessage
            ? ['message' => $newMessage, 'warnings' => []]
            : toIcu($source[$oldKey]);

        $target[$newKey] = $icu['message'];

        $io->writeln(\sprintf('<info>%s</info>: %s', $newKey, $icu['message']));
        foreach ($icu['warnings'] as $warning) {
            $io->writeln(\sprintf('  <fg=yellow>! %s</>', $warning));
        }

        dumpYaml($targetFile, $target);
    }

    removeYamlKey($sourceFile, $oldKey);

    $io->newLine();
    $io->writeln(\sprintf('%d file(s) updated', $files));

    return Command::SUCCESS;
};

new SingleCommandApplication()
    ->setName('Migrate translation key')
    ->addArgument('old-key', InputArgument::REQUIRED, 'key in '.SOURCE_DOMAIN)
    ->addArgument('new-key', InputArgument::REQUIRED, 'key in '.TARGET_DOMAIN)
    ->addArgument('message', InputArgument::OPTIONAL, 'english ICU message, defaults to converting the old one')
    ->setCode($command)
    ->run();
