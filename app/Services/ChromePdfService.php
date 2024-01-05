<?php

namespace App\Services;

use Nuzkito\ChromePdf\ChromePdf;

class ChromePdfService extends ChromePdf
{
    public function generateFromUrl($url): void
    {
        $command = sprintf(
            '%s --no-sandbox --headless --disable-gpu --print-to-pdf-no-header --no-pdf-header-footer --print-to-pdf=%s %s 2>&1',
            escapeshellarg($this->getBinary()),
            escapeshellarg($this->getOutput()),
            escapeshellarg($url)
        );
        exec($command);
    }

    public function generateFromHtml($html): void
    {
        $this->generateFromUrl('data:text/html,' . rawurlencode($html));
    }


    /**
     * @return mixed
     */
    public function getBinary()
    {
        return $this->binary;
    }

    /**
     * @param mixed $binary
     */
    public function setBinary($binary): void
    {
        $this->binary = $binary;
    }

    /**
     * @return mixed
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * @param mixed $output
     */
    public function setOutput($output): void
    {
        $this->output = $output;
    }

}
