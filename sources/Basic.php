<?php

/*
 * The MIT License
 *
 * Copyright 2017 Daniel Popiniuc.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace danielgp\structure_validated;

/**
 *
 * @author Daniel Popiniuc
 */
trait Basic
{

    use \danielgp\common_lib\CommonCode;

    protected $tApp = null;

    private function handleLocalizationStructureValidated($appSettings)
    {
        $this->handleLocalizationStructureValidatedInputsIntoSession($appSettings);
        $this->handleLocalizationStructureValidatedSafe($appSettings);
        $localizationFile = __DIR__ . '\\locale\\' . $this->tCmnSession->get('lang') . '\\LC_MESSAGES\\structure-validated.mo';
        $translations     = new \Gettext\Translations;
        $translations->addFromMoFile($localizationFile);
        $this->tApp       = new \Gettext\Translator();
        $this->tApp->loadTranslations($translations);
    }

    private function handleLocalizationStructureValidatedInputsIntoSession($appSettings)
    {
        if (is_null($this->tCmnSuperGlobals->get('lang')) && is_null($this->tCmnSession->get('lang'))) {
            $this->tCmnSession->set('lang', $appSettings['Default Language']);
        } elseif (!is_null($this->tCmnSuperGlobals->get('lang'))) {
            $this->tCmnSession->set('lang', filter_var($this->tCmnSuperGlobals->get('lang'), FILTER_SANITIZE_STRING));
        }
    }

    /**
     * To avoid potential language injections from other applications that do not applies here
     *
     * @param type $appSettings
     */
    private function handleLocalizationStructureValidatedSafe($appSettings)
    {
        if (!array_key_exists($this->tCmnSession->get('lang'), $appSettings['Available Languages'])) {
            $this->tCmnSession->set('lang', $appSettings['Default Language']);
        }
    }

    /**
     * returns an array with non-standard holidays from a JSON file
     *
     * @param string $fileBaseName
     * @return mixed
     */
    protected function readTypeFromJsonFileStructureValidated($filePath, $fileBaseName)
    {
        $fName       = $filePath . DIRECTORY_SEPARATOR . $fileBaseName . '.min.json';
        $fJson       = fopen($fName, 'r');
        $jSonContent = fread($fJson, filesize($fName));
        fclose($fJson);
        return json_decode($jSonContent, true);
    }

    private function setFooterHtml($appSettings)
    {
        return $this->setFooterCommon($this->setUpperRightBoxLanguages($appSettings['Available Languages'])
                . '<div class="resetOnly author">&copy; ' . date('Y') . ' '
                . $appSettings['Copyright Holder'] . '</div>');
    }

    private function setHeaderHtml($appSettings)
    {
        $headerParameters = [
            'css'        => $appSettings['Components']['Cascade Style Sheets'],
            'javascript' => $appSettings['Components']['JavaScript'],
            'lang'       => str_replace('_', '-', $this->tCmnSession->get('lang')),
            'title'      => $appSettings['Name'],
        ];
        return $this->setHeaderCommon($headerParameters)
            . '<h1>' . $appSettings['Name'] . '</h1>';
    }
}
