import type { EmsModule } from '../types/emsModule'

const SELECTOR = '.text-counter[data-counter-label]'
const REVISION_SELECTOR = 'form[name="revision"]'
const WYSIWYG_SELECTOR = '.ems-wysiwyg, .ems-wysiwyg-revision'

export default class TextCounter implements EmsModule {
    private readonly initialized = new WeakSet<HTMLElement>()

    public load(target: Document | HTMLElement): void {
        target.querySelectorAll<HTMLElement>(SELECTOR).forEach((span) => this.init(span))
    }

    private init(span: HTMLElement): void {
        if (this.initialized.has(span) || !span.closest(REVISION_SELECTOR)) {
            return
        }

        const input = document.getElementById(span.dataset.counterTarget ?? '')

        if (!this.isCountable(input)) {
            return
        }

        this.initialized.add(span)

        const label = span.dataset.counterLabel ?? ''
        const update = (): void => {
            span.textContent = label.replace('{count}', `${input.value.length}`)
        }

        input.addEventListener('keyup', update)
        update()
    }

    private isCountable(
        input: HTMLElement | null
    ): input is HTMLInputElement | HTMLTextAreaElement {
        if (!(input instanceof HTMLInputElement) && !(input instanceof HTMLTextAreaElement)) {
            return false
        }

        return !input.matches(WYSIWYG_SELECTOR)
    }
}
