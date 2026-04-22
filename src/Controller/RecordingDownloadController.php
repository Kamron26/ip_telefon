<?php

namespace App\Controller;

use App\Entity\Recording;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class RecordingDownloadController extends AbstractController
{
    public function __invoke(Recording $recording): BinaryFileResponse
    {
        $path = $recording->getFilePath();

        if (!$path || !is_file($path)) {
            throw $this->createNotFoundException('Recording file not found');
        }

        $response = new BinaryFileResponse($path);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_INLINE,
            basename($path)
        );

        return $response;
    }
}
