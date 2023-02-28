<?php

namespace YusamHub\PdfExt\Tests;

use YusamHub\PdfExt\RenderPdf;
use PHPUnit\Framework\TestCase;

class PdfTest extends TestCase
{
    /*public function testCombineVertical()
    {
        $renderPdf = new RenderPdf();

        $renderPdf->setPdfFilename(__DIR__ . '/../pdf/test_vertical.pdf');

        $this->assertTrue(file_exists($renderPdf->getPdfFilename()));

        $content = $renderPdf->renderedContent(function(RenderPdf $owner, int $pageNo){
            $owner->cellOne(20,20,100,"!!!!!!!!!!!!!!! Тест русский Test English");
        });

        file_put_contents(__DIR__ . '/../output/test_vertical.pdf', $content);

        $this->assertTrue(true);
    }

    public function testCombineHorizontal()
    {
        $renderPdf = new RenderPdf("L");

        $renderPdf->setPdfFilename(__DIR__ . '/../pdf/test_horizontal.pdf');

        $this->assertTrue(file_exists($renderPdf->getPdfFilename()));

        $content = $renderPdf->renderedContent(function(RenderPdf $owner, int $pageNo){
            $owner->cellOne(20,20,100,"!!!!!!!!!!!!!!! Тест русский Test English");
        });

        file_put_contents(__DIR__ . '/../output/test_horizontal.pdf', $content);

        $this->assertTrue(true);
    }*/

    public function testEmptyRenderPdf()
    {
        $renderPdf = new RenderPdf();


        $content = $renderPdf->renderedContent(function(RenderPdf $owner, int $pageNo){
            $owner->cellOne(20,20,100,"!!!!!!!!!!!!!!! Тест русский Test English");
        });

        file_put_contents(__DIR__ . '/../output/empty.pdf', $content);

        $this->assertTrue(true);
    }
}