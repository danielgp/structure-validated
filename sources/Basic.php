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

    protected function handleLocalizationStructureValidated($appSettings)
    {
        $this->handleLocalizationStructureValidatedInputsIntoSession($appSettings);
        $this->handleLocalizationStructureValidatedSafe($appSettings);
        $localizationFile = __DIR__ . '\\locale\\' . $this->tCmnSession->get('lang')
            . '\\LC_MESSAGES\\structure-validated.mo';
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

    protected function localeSV($inputString)
    {
        return $this->tApp->gettext(htmlspecialchars($inputString));
    }

    protected function localeSVextended($inputString, $features = null)
    {
        if (isset($features['prefix'])) {
            $translated = $this->localeSV($features['prefix'] . $inputString);
            if ($translated === $features['prefix'] . $inputString) {
                $sReturn = $inputString;
            } else {
                $sReturn = $translated;
            }
        }
        return $sReturn;
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

    protected function setFieldLocalized($tblName, $fldName)
    {
        return ' AS `' . $this->localeSV('i18n_MySQL_Field__' . $tblName . '|' . $fldName) . '`';
    }

    protected function setFooterHtml($appSettings)
    {
        return '</section>' . $this->setFooterCommon(''
                . $this->setUpperRightBoxLanguages($appSettings['Available Languages'])
                . '<footer class="resetOnly author">&copy; ' . $appSettings['Copyright Holder'] . ', ' . date('Y')
                . '</footer>');
    }

    protected function setHeaderHtml($appSettings, $menuSettings)
    {
        $appSettings['Components']['JavaScript'][0] = str_replace('LC_CT', $appSettings['Locale To ISO3'
            . ''][$this->tCmnSession->get('lang')], $appSettings['Components']['JavaScript'][0]);
        $headerParameters                           = [
            'css'        => $appSettings['Components']['Cascade Style Sheets'],
            'javascript' => $appSettings['Components']['JavaScript'],
            'lang'       => str_replace('_', '-', $this->tCmnSession->get('lang')),
            'title'      => $appSettings['Name'],
        ];
        return $this->setHeaderCommon($headerParameters)
            . '<div id="SVmenu">' . $this->setMenu($menuSettings) . '</div><!-- main-menu end -->' . $this->setMenuJS()
            . '<header id="PageHeader">' . '<h1>' . $appSettings['Name'] . '</h1>' . '</header>'
            . '<section id="ContentContainer">';
    }

    private function setMenu($menuSettings)
    {
        $sReturn    = null;
        $remembered = null;
        foreach ($menuSettings as $value) {
            if ($value['Parent'] === true) {
                $sReturn[] = (!is_null($remembered) ? '</ul></li>' : '')
                    . '<li><a href="#"><i class="' . $value['Icon'] . '"></i>'
                    . $this->localeSVextended($value['ID'], ['prefix' => 'i18n_MenuItem_']) . '</a>';
            } else {
                if ($value['Parent'] == $remembered['ID']) {
                    $sReturn[] = '<h2><i class="' . $remembered['Icon'] . '"></i>'
                        . $this->localeSVextended($remembered['ID'], ['prefix' => 'i18n_MenuItem_']) . '</h2><ul>';
                }
                $sReturn[] = '<li><a href="' . $value['LinkPrefix'] . $value['ID'] . '&amp;T=' . $value['Table']
                    . '&amp;Q=' . $value['QueryListing'] . '">'
                    . '<i class="' . $value['Icon'] . '"></i>'
                    . $this->localeSVextended($value['ID'], ['prefix' => 'i18n_MenuItem_']) . '</a></li>';
            }
            $remembered = $value;
        }
        return '<div id="SHmenu"><nav><h2><i class="fa fa-home"></i>&nbsp;</h2>'
            . '<ul>' . implode('', $sReturn) . '</li></ul></nav></div><!-- SHmenu end -->';
    }

    private function setMenuJS()
    {
        return $this->setJavascriptContent(implode('', [
                '$("#SHmenu").css("visibility", "hidden");',
                '$(document).ready(function(){',
                ' $("#SHmenu").multilevelpushmenu({',
                implode(',', [
                    'backItemClass: "backItemClass"',
                    'backItemIcon: "fa fa-angle-right"',
                    'backText: "' . $this->localeSV('i18n_Back') . '"',
                    'collapsed: true',
                    'containersToPush: [$("#PageHeader"),$("#ContentContainer")]',
                    'fullCollapse: false',
                    'groupIcon: "fa fa-angle-left"',
                    'menuWidth: 300',
                    'mode: "overlap"',
                    'preventItemClick: false',
                ]),
                ' });',
                ' $("#SHmenu").css("visibility", "visible");',
                '});',
        ]));
    }
}
