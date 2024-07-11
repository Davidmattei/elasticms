<?php

declare(strict_types=1);

namespace EMS\CoreBundle\Controller;

use EMS\CoreBundle\Core\DataTable\DataTableFactory;
use EMS\CoreBundle\Core\UI\AjaxService;
use EMS\CoreBundle\DataTable\Type\WysiwygUploadedFileDataTableType;
use EMS\CoreBundle\Form\Form\TableType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class UploadedFileWysiwygController extends AbstractController
{
    use CoreControllerTrait;

    public function __construct(
        private readonly AjaxService $ajax,
        private readonly DataTableFactory $dataTableFactory
    ) {
    }

    public function index(Request $request): Response
    {
        $table = $this->dataTableFactory->create(WysiwygUploadedFileDataTableType::class);
        $form = $this->createForm(TableType::class, $table);
        $form->handleRequest($request);

        return $this->render('@EMSCore/uploaded-file-wysiwyg/index.html.twig', [
            'form' => $form->createView(),
            'CKEditorFuncNum' => $request->query->get('CKEditorFuncNum') ?: 0,
        ]);
    }

    public function modal(): Response
    {
        $table = $this->dataTableFactory->create(WysiwygUploadedFileDataTableType::class);
        $form = $this->createForm(TableType::class, $table);

        return $this->ajax->newAjaxModel('@'.$this->getTemplateNamespace().'/uploaded-file-wysiwyg/modal.html.twig')
            ->setBody('modalBody', ['form' => $form->createView()])
            ->getResponse();
    }
}
