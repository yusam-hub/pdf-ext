<?php

namespace YusamHub\PdfExt\Ufpdf;

class UfPdfExt extends UfPdf
{
    const EXT_FONT_FAMILY_CALIBRI = 'calibri';
    const EXT_FONT_SIZE_DEFAULT = 12;
    const EXT_RENDER_TYPE_PROPERTY_AS_DEFAULT = 0;
    const EXT_RENDER_TYPE_PROPERTY_AS_KEY_VALUE = 1;
    const EXT_RENDER_TYPE_PROPERTY_AS_SIMPLE_PRODUCT = 2;

    protected bool $isDebugging = false;

    /**
     * @var string
     */
    private string $extFontFamily = self::EXT_FONT_FAMILY_CALIBRI;

    /**
     * @var int
     */
    private int $extFontSize = self::EXT_FONT_SIZE_DEFAULT;

    /**
     * UfPdfExt constructor.
     * @param string $orientation
     * @param string $unit
     * @param string $format
     */
    public function __construct($orientation = 'P', $unit = 'mm', $format = 'A4')
    {
        parent::__construct($orientation, $unit, $format);

        $this->initFonts();

        $this->extSetFontSize($this->extFontSize);
        $this->extResetColors();
    }

    public function initFonts()
    {
        $this->AddFont(self::EXT_FONT_FAMILY_CALIBRI, '', 'calibri.php');
        $this->AddFont(self::EXT_FONT_FAMILY_CALIBRI, 'I', 'calibrii.php');
        $this->AddFont(self::EXT_FONT_FAMILY_CALIBRI, 'B', 'calibrib.php');
    }

    /**
     *
     */
    public function extResetColors()
    {
        $this->extSetTextColor();
        $this->extSetFillColor();
        $this->extSetDrawColor();
    }

    /**
     * @return bool
     */
    public function isDebugging(): bool
    {
        return $this->isDebugging;
    }

    public function webColorToRgb(string $webColor = "#000000"): array
    {
        list($r, $g, $b) = sscanf($webColor, "#%02x%02x%02x");

        return [
            $r,
            $g,
            $b
        ];
    }

    /**
     * @param string $webColor
     */
    public function extSetTextColor(string $webColor = "#000000")
    {
        $rgb = $this->webColorToRgb($webColor);
        $this->SetTextColor($rgb[0], $rgb[1], $rgb[2]);
    }

    /**
     * @param string $webColor
     */
    public function extSetDrawColor(string $webColor = "#000000")
    {
        $rgb = $this->webColorToRgb($webColor);
        $this->SetDrawColor($rgb[0], $rgb[1], $rgb[2]);
    }

    /**
     * @param string $webColor
     */
    public function extSetFillColor(string $webColor = "#FFFFFF")
    {
        $rgb = $this->webColorToRgb($webColor);
        $this->SetFillColor($rgb[0], $rgb[1], $rgb[2]);
    }

    /**
     * @return string
     */
    public function extGetFontFamily(): string
    {
        return $this->extFontFamily;
    }

    /**
     * @param string $extFontFamily
     */
    public function extSetFontFamily(string $extFontFamily)
    {
        $this->extFontFamily = $extFontFamily;
    }

    /**
     * @return int
     */
    public function extGetFontSize(): int
    {
        return $this->extFontSize;
    }

    /**
     * @param int $extFontSize
     * @param string $fontStyle
     */
    public function extSetFontSize(int $extFontSize, string $fontStyle = '')
    {
        $this->extFontSize = $extFontSize;
        $this->SetFont($this->extGetFontFamily(), $fontStyle, $this->extGetFontSize());
    }

    /**
     * @return float
     */
    public function extGetFontLineHeight()
    {
        return $this->extGetFontSize() * 0.5;
    }

    /**
     * @param string $value
     * @return float|int
     */
    public function extGetLineWidth(string $value)
    {
        return $this->GetStringWidth($value);
    }

    /**
     * @return float|int|mixed
     */
    protected function extGetPageWidth()
    {
        return $this->w;
    }

    /**
     * @return float|int|mixed
     */
    protected function extGetPageHeight()
    {
        return $this->h;
    }

    /**
     * @param float $x
     * @param float $y
     * @param float $w
     * @param $text
     * @param string $align
     * @return float
     */
    public function cellOne(float $x, float $y, float $w, $text, string $align = "C"): float
    {
        $this->breakPageIfRequire($this->extGetFontLineHeight(), $y);
        $this->SetXY($x, $y);
        $this->Cell($w, $this->extGetFontLineHeight(), strval($text) , $this->isDebugging(), 0, $align);
        return $y + $this->extGetFontLineHeight();
    }

    /**
     * @param float $x
     * @param float $y
     * @param float $w
     * @param $text
     * @param int $textLineSpace
     * @param string $align
     * @return float
     */
    public function cellOneMultipleTextLine(float $x, float $y, float $w, $text, int $textLineSpace = 0, string $align = "C"): float
    {
        $strings = $this->splitByLength(strval($text), $w, " ", function(string $line){
            return $this->extGetLineWidth($line);
        });
        $tmpY = $y;
        foreach($strings as $string) {
            $this->breakPageIfRequire($this->extGetFontLineHeight() + $textLineSpace, $tmpY);
            $this->cellOne($x, $tmpY, $w, $string, $align);
            $tmpY = $tmpY + $this->extGetFontLineHeight() + $textLineSpace;
        }
        return $tmpY;
    }

    /**
     * @param float $x
     * @param float $y
     * @param float $tableWidth
     * @param array $pairKeyValue
     * @param float $keyFieldWidthPercent
     * @param int $renderType
     * @return float
     */
    public function cellsAsPropertyTable(float $x, float $y, float $tableWidth,
                                            array $pairKeyValue = [],
                                            float $keyFieldWidthPercent = 50.0,
                                            int $renderType = self::EXT_RENDER_TYPE_PROPERTY_AS_KEY_VALUE): float
    {
        $pairKeyValue = array_filter($pairKeyValue, function($v){
            return !empty($v);
        });

        $keyWidth = $tableWidth * $keyFieldWidthPercent / 100;

        switch ($renderType) {
            case self::EXT_RENDER_TYPE_PROPERTY_AS_KEY_VALUE:
                $fontValue = ["B", ""];
                $fillValue = [1, 0];
                $alignValue = ["R", "L"];
                break;
            default:
                $fontValue = ["", ""];
                $fillValue = [0, 0];
                $alignValue = ["L", "L"];
                break;
        }

        $index = 0;
        $c = count(array_keys($pairKeyValue));
        foreach ($pairKeyValue as $name => $value ) {
            $index++;
            if (($renderType === self::EXT_RENDER_TYPE_PROPERTY_AS_SIMPLE_PRODUCT) && ($c >= 3)) {
                $fontValue = ["", ""];
                $fillValue = [0, 0];
                $alignValue = ["L", "R"];
                if ($index === 1) {
                    $fontValue = ["B", "B"];
                    $fillValue = [1, 1];
                    $alignValue = ["C", "C"];
                } elseif ($index === $c) {
                    $fontValue = ["B", ""];
                    $fillValue = [1, 0];
                    $alignValue = ["R", "R"];
                }
            }
            $names = $this->splitByLength(strval($name), $keyWidth, " ", function(string $line){
                return $this->extGetLineWidth($line);
            });

            $values = $this->splitByLength(strval($value), $tableWidth - $keyWidth, " ", function(string $line){
                return $this->extGetLineWidth($line);
            });

            $this->SetFont($this->extGetFontFamily(), $fontValue[0], $this->extGetFontSize());

            $currentHeight = max(count($names), count($values)) * $this->extGetFontLineHeight();
            $this->breakPageIfRequire($currentHeight, $y);

            $this->SetXY($x, $y);
            $this->Cell($keyWidth, $currentHeight, "" , 1, 0, "C", $fillValue[0]);

            $this->SetXY($x+$keyWidth, $y);
            $this->Cell($tableWidth - $keyWidth, $currentHeight, "" , 1, 0, "C", $fillValue[1]);

            $tmpY1 = $y;
            foreach($names as $string) {
                $this->cellOne($x, $tmpY1, $keyWidth, $string, $alignValue[0]);
                $tmpY1 = $tmpY1 + $this->extGetFontLineHeight();
            }

            $this->SetFont($this->extGetFontFamily(), $fontValue[1], $this->extGetFontSize());

            $tmpY2 = $y;
            foreach($values as $string) {
                $this->cellOne($x + $keyWidth, $tmpY2, $tableWidth - $keyWidth, $string, $alignValue[1]);
                $tmpY2 = $tmpY2 + $this->extGetFontLineHeight();
            }
            $y = max($tmpY1, $tmpY2);
        }

        return $y;
    }

    /**
     * исправленная функция из скрипта @link http://www.fpdf.org/?go=script&id=3
     * @param array $data
     * @param array $widths
     * @param array $aligns
     * @param bool $border
     * @param array $fills
     * @param $rowHeight
     * @return void
     */
    public function MultiCellRow(array $data, array $widths, array $aligns = [], bool $border = true, array $fills = [], $rowHeight = null)
    {
        $rowHeight = $rowHeight ?? $this->FontSize;

        // calculate last row width, if $w === 0
        $tmpX = 0;
        foreach ($widths as &$width) {
            if ($width === 0) {
                $width = $this->w - $this->x - $this->rMargin - $tmpX;
                break;
            } else {
                $tmpX += $width;
            }
        }
        unset($width, $tmpX);

        //Calculate the height of the row
        $nb=0;
        for($i=0;$i<count($data);$i++)
            $nb=max($nb,$this->NbLines($widths[$i],$data[$i]));
        $h=$rowHeight*$nb;
        //Issue a page break first if needed
        $this->CheckPageBreak($h);
        //Draw the cells of the row
        for($i=0;$i<count($data);$i++)
        {
            $w=$widths[$i];
            $a=isset($aligns[$i]) ? $aligns[$i] : 'L';
            //Save the current position
            $x=$this->GetX();
            $y=$this->GetY();
            $fill = false;
            if (!empty($fills)) {
                if(is_array($fills)) {
                    $fill = $fills[$i] ?? 0;
                } else {
                    $fill = 1;
                }
            }
            // Print the text
            $this->MultiCell($w, $rowHeight, $data[$i], 0, $a, $fill);

            if ($fill) {
                $realH = $this->y - $y;
                $toFill = intval(($h - $realH) / $rowHeight);
                for ($fu=0; $fu < $toFill; $fu++) {
                    $this->Cell($w, $rowHeight, '', 0, 0, '', true);
                }
            }


            //Draw the border
            if ($border) {
                $this->Rect($x,$y,$w,$h);
            }
            //Put the position to the right of the cell
            $this->SetXY($x+$w,$y);
        }
        //Go to the next line
        $this->Ln($h);
    }

    /**
     * @param $h
     * @param $y
     * @param $addpage
     * @return void
     */
    public function CheckPageBreak($h=0, $y=null, $addpage=true)
    {
        //If the height h would cause an overflow, add a new page immediately
        if ($this->GetY()+$h > $this->PageBreakTrigger) {
            $this->AddPage($this->CurOrientation);
        }
    }

    /**
     * @param string $str
     * @param int $maxLength
     * @param string $delimiter
     * @param \Closure|null $lineLengthCallback
     * @return array
     */
    public function splitByLength(string $str, int $maxLength, string $delimiter = " ", \Closure $lineLengthCallback = null): array
    {
        $words = explode($delimiter, $str);

        $lines = [];

        $lineInd = 0;
        foreach ($words as $word) {

            if (!isset($lines[$lineInd])) {
                $lines[$lineInd] = [];
            }

            $lines[$lineInd][] = $word;

            $line = implode($delimiter, $lines[$lineInd]);

            $lineLen = mb_strlen($line);
            if (is_callable($lineLengthCallback)) {
                $lineLen = $lineLengthCallback($line);
            }

            if ($lineLen > $maxLength) {
                $word = array_pop($lines[$lineInd]);
                $lineInd++;
                $lines[$lineInd] = [$word];
            }
        }

        foreach ($lines as &$line) {
            $line = implode($delimiter, $line);
        }

        return $lines;
    }

    /**
     * @param float $nextHeight
     * @param float $y
     * @return void
     */
    public function breakPageIfRequire(float $nextHeight, float &$y)
    {
        if ($y + $nextHeight + $this->bMargin >= $this->extGetPageHeight()) {
            $this->AddPage($this->CurOrientation);
            $y = $this->tMargin;
        }
    }

    /**
     * @param $value
     * @param int $truncatePrecision
     * @param string $thousandSeparator
     * @return string
     */
    public function getMoneyFormat($value, int $truncatePrecision = 2, string $thousandSeparator = ' '): string
    {
        $valueString = strval($value);

        if (!preg_match('/^[-]?([0-9]+[.][0-9]+|[0-9]+)$/', $valueString)) {
            return $value;
        }

        if ($truncatePrecision < 0) {
            $truncatePrecision = 0;
        }

        if (!strstr($valueString, ".")) {
            $valueString .= '.';
        }

        list($intValue, $precisionValue) = explode(".", $valueString);

        if ($truncatePrecision) {
            $precisionValue = str_pad(substr($precisionValue, 0, $truncatePrecision), $truncatePrecision, "0");
        }

        $minus = '';
        $intValues = preg_split('//', $intValue, -1, PREG_SPLIT_NO_EMPTY);
        if ($intValues[0] === '-') {
            $minus = '-';
            array_shift($intValues);
        }

        $intValues = array_chunk(array_reverse($intValues), 3);
        $intValue = [];
        while ($v = array_pop($intValues)) {
            $v = array_reverse($v);
            $intValue[] = implode("", $v);
        }

        return (($minus) ? $minus . " ": "") . implode($thousandSeparator, $intValue) . (($truncatePrecision) ? '.' . $precisionValue : '');
    }

    /**
     * @param float $x
     * @param float $y
     * @param float $tableWidth
     * @param array $productInfo
     * @return float
     */
    public function cellsAsProductTitlePriceCountAmountTable(float $x, float $y, float $tableWidth, array $productInfo): float
    {
        $roundPrecision = 2;
        $fontNormal = $this->extGetFontSize();
        /*'invoiceProductInfo' => [
            'columnNames' => [
                'pos' => 'NN',
                'title' => 'Description',
                'price' => 'Price (USD)',
                'count' => 'Qty',
                'amount' => 'Amount (USD)',
                'total' => 'Total (USD):',
            ],
            'items' => [
                ['title' => 'product 1 product 1 product 1 product 1 product 1 product 1 product 1 product 1', 'price' => 1, 'count' => 1, 'amount' => 1],
            ],
            'roundPrecision' => 2,
            'totalAmount' => 1,
        ],*/
        $columnPosWidth = $tableWidth * 0.1;
        $columnTitleWidth = $tableWidth * 0.5;
        $columnPriceWidth = $tableWidth * 0.15;
        $columnCountWidth = $tableWidth * 0.1;
        $columnAmountWidth = $tableWidth * 0.15;
        if (isset($productInfo['roundPrecision'])) {
            $roundPrecision = (int) $productInfo['roundPrecision'];
        }
        if (!isset($productInfo['columnNames']['pos'])) {
            $productInfo['columnNames']['pos'] = 'undefined';
        }
        if (!isset($productInfo['columnNames']['title'])) {
            $productInfo['columnNames']['title'] = 'undefined';
        }
        if (!isset($productInfo['columnNames']['price'])) {
            $productInfo['columnNames']['price'] = 'undefined';
        }
        if (!isset($productInfo['columnNames']['count'])) {
            $productInfo['columnNames']['count'] = 'undefined';
        }
        if (!isset($productInfo['columnNames']['amount'])) {
            $productInfo['columnNames']['amount'] = 'undefined';
        }
        if (!isset($productInfo['columnNames']['total'])) {
            $productInfo['columnNames']['total'] = 'undefined';
        }

        /**
         * Header
         */
        $this->extSetFontSize($fontNormal, 'B');
        $this->breakPageIfRequire($this->extGetFontLineHeight(), $y);

        $this->SetXY($x, $y);
        $this->Cell($columnPosWidth, $this->extGetFontLineHeight(), strval($productInfo['columnNames']['pos']) , 1, 0, "C", 0);

        $this->SetXY($x + $columnPosWidth, $y);
        $this->Cell($columnTitleWidth, $this->extGetFontLineHeight(), strval($productInfo['columnNames']['title']) , 1, 0, "C", 0);

        $this->SetXY($x + $columnPosWidth + $columnTitleWidth, $y);
        $this->Cell($columnPriceWidth, $this->extGetFontLineHeight(), strval($productInfo['columnNames']['price']) , 1, 0, "C", 0);

        $this->SetXY($x + $columnPosWidth + $columnTitleWidth + $columnPriceWidth, $y);
        $this->Cell($columnCountWidth, $this->extGetFontLineHeight(), strval($productInfo['columnNames']['count']) , 1, 0, "C", 0);

        $this->SetXY($x + $columnPosWidth + $columnTitleWidth + $columnPriceWidth + $columnCountWidth, $y);
        $this->Cell($columnAmountWidth, $this->extGetFontLineHeight(), strval($productInfo['columnNames']['amount']) , 1, 0, "C", 0);

        $y = $y + $this->extGetFontLineHeight();

        /**
         * Items
         */
        $this->extSetFontSize($fontNormal, '');

        $pos = 1;
        $totalAmount = 0;
        if (!isset($productInfo['items'])) $productInfo['items'] = [];

        foreach($productInfo['items'] as $item) {
            if (!isset( $item['price']))  $item['price'] = 0;
            if (!isset( $item['count']))  $item['count'] = 0;

            $titles = $this->splitByLength(strval($item['title'] ?? 'undefined'), $columnTitleWidth, " ", function(string $line){
                return $this->extGetLineWidth($line);
            });
            $currentHeight = max(count($titles), 1) * $this->extGetFontLineHeight();
            $this->breakPageIfRequire($currentHeight, $y);

            $this->SetXY($x, $y);
            $this->Cell($columnPosWidth, $currentHeight, "" , 1, 0, "C", 0);
            $this->SetXY($x, $y);
            $this->Cell($columnPosWidth, $this->extGetFontLineHeight(), strval($pos) , 0, 0, "C", 0);

            $this->SetXY($x + $columnPosWidth, $y);
            $this->Cell($columnTitleWidth, $currentHeight, "" , 1, 0, "", 0);
            $tmpY1 = $y;
            foreach($titles as $string) {
                $this->cellOne($x + $columnPosWidth, $tmpY1, $columnTitleWidth, $string, '');
                $tmpY1 = $tmpY1 + $this->extGetFontLineHeight();
            }

            $this->SetXY($x + $columnPosWidth + $columnTitleWidth, $y);
            $this->Cell($columnPriceWidth, $currentHeight, "" , 1, 0, "C", 0);
            $this->SetXY($x + $columnPosWidth + $columnTitleWidth, $y);
            $this->Cell($columnPriceWidth, $this->extGetFontLineHeight(), $this->getMoneyFormat($item['price'], $roundPrecision) , 0, 0, "R", 0);

            $this->SetXY($x + $columnPosWidth + $columnTitleWidth + $columnPriceWidth, $y);
            $this->Cell($columnCountWidth, $currentHeight, "" , 1, 0, "C", 0);
            $this->SetXY($x + $columnPosWidth + $columnTitleWidth + $columnPriceWidth, $y);
            $this->Cell($columnCountWidth,  $this->extGetFontLineHeight(), strval($item['count']) , 0, 0, "C", 0);

            $amount = round((float) $item['price'] * (float) $item['count'], $roundPrecision);
            if (isset($item['amount'])) {
                $amount = (float) $item['amount'];
            }
            $totalAmount = $totalAmount + $amount;
            $this->SetXY($x + $columnPosWidth + $columnTitleWidth + $columnPriceWidth + $columnCountWidth, $y);
            $this->Cell($columnAmountWidth, $currentHeight, "" , 1, 0, "R", 0);
            $this->SetXY($x + $columnPosWidth + $columnTitleWidth + $columnPriceWidth + $columnCountWidth, $y);
            $this->Cell($columnAmountWidth, $this->extGetFontLineHeight(), $this->getMoneyFormat($amount, $roundPrecision), 0, "R", 0);

            $pos++;
            $y = $y + $currentHeight;
        }

        /**
         * Footer
         */
        $this->extSetFontSize($fontNormal, 'B');
        $this->breakPageIfRequire($this->extGetFontLineHeight(), $y);

        $this->SetXY($x, $y);
        $this->Cell($columnPosWidth + $columnTitleWidth + $columnPriceWidth + $columnCountWidth, $this->extGetFontLineHeight(), strval($productInfo['columnNames']['total']), 1, 0, "R", 0);

        $totalAmount = round($totalAmount, $roundPrecision);
        if (isset($productInfo['totalAmount'])) {
            $totalAmount = (float) $productInfo['totalAmount'];
        }
        $this->SetXY($x + $columnPosWidth + $columnTitleWidth + $columnPriceWidth + $columnCountWidth, $y);
        $this->Cell($columnAmountWidth, $this->extGetFontLineHeight(), $this->getMoneyFormat($totalAmount, $roundPrecision), 1,  0,"R", 0);

        $y = $y + $this->extGetFontLineHeight();

        return $y;
    }

    /**
     * @param array $strings
     * @return array
     */
    protected function fetchPairKeyValue(array $strings): array
    {
        $pairKeyValues = [];
        foreach($strings as $item) {
            list($title, $value) = explode(":", $item);
            $title = strval($title);
            $value = strval($value);
            $pairKeyValues[trim($title)] = trim($value);
        }
        return $pairKeyValues;
    }
}