<?php

namespace YusamHub\PdfExt;

use YusamHub\PdfExt\Ufpdf\UfPdfExt;

class RenderPdf extends UfPdfExt
{
    protected string $pdfFilename = '';

    /**
     * @return string
     */
    public function getPdfFilename(): string
    {
        return $this->pdfFilename;
    }

    /**
     * @param string $pdfFilename
     */
    public function setPdfFilename(string $pdfFilename): void
    {
        $this->pdfFilename = $pdfFilename;
    }

    /**
     * @param int $pageNo
     */
    protected function renderPage(int $pageNo): void
    {

    }

    /**
     * @return string
     */
    protected function getRenderContentName(): string
    {
        return basename($this->getPdfFilename());
    }

    /**
     * @param \Closure|null $callback - function($this, $pageNo){}
     * @param int|null $pageCount
     * @return void
     */
    protected function renderPages(?\Closure $callback = null, ?int $pageCount = 1): void
    {
        $fileExists = file_exists($this->getPdfFilename());

        if ($fileExists) {
            $pageCount = $this->setSourceFile($this->getPdfFilename());
        }

        if ($pageCount <= 0) {
            return;
        }

        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++)
        {
            $this->AddPage();

            if ($fileExists) {
                $tplIdx = $this->importPage($pageNo);
                $this->useTemplate($tplIdx, 0, 0);
            }

            if (is_callable($callback)) {
                $callback($this, $pageNo);
            }

            $this->renderPage($pageNo);
        }
    }

    /**
     * @param \Closure|null $callback - function($this, $pageNo){}
     * @param int|null $pageCount
     * @return string
     */
    public function renderedContent(?\Closure $callback = null, ?int $pageCount = 1): string
    {
        $this->renderPages($callback, $pageCount);

        return $this->Output($this->getRenderContentName(), 'S');
    }
}