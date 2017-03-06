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
 * Description of StructureValidated
 *
 * @author Daniel Popiniuc
 */
class StructureValidated extends SQLqueries
{

    private $inElmnts = null;

    public function __construct()
    {
        $this->inElmnts = $this->readTypeFromJsonFileStructureValidated('config', 'interfaceElements');
        $this->initializeSprGlbAndSession();
        $this->handleLocalizationStructureValidated($this->inElmnts['Application']);
        $action         = filter_var($this->tCmnSuperGlobals->get('action'), FILTER_SANITIZE_STRING);
        $targetID       = filter_var($this->tCmnSuperGlobals->get('ID'), FILTER_SANITIZE_STRING);
        $targetTable    = filter_var($this->tCmnSuperGlobals->get('T'), FILTER_SANITIZE_STRING);
        $listingQuery   = filter_var($this->tCmnSuperGlobals->get('Q'), FILTER_SANITIZE_STRING);
        if (in_array($action, ['add', 'edit'])) {
            echo $this->setPerformActions($action, $targetID, $targetTable, $listingQuery);
            return '';
        }
        echo $this->setHeaderHtml($this->inElmnts['Application'], $this->inElmnts['Menu']);
        if ($action === '') {
            echo '<p>' . $this->tApp->gettext('i18n_WIP') . '</p>';
        } else {
            echo $this->setPerformActions($action, $targetID, $targetTable, $listingQuery);
        }
        echo $this->setFooterHtml($this->inElmnts['Application']);
    }

    private function getTableTranslatedFields($dbName, $tblName)
    {
        $tableFields = $this->getMySQLlistColumns([
            'TABLE_SCHEMA' => $dbName,
            'TABLE_NAME'   => $tblName,
        ]);
        $translated  = true;
        foreach ($tableFields as $value) {
            $lString = 'i18n_MySQL_Field__' . $tblName . '|' . $value['COLUMN_NAME'];
            if ($this->localeSV($lString) == $lString) {
                $translated = false;
            } else {
                $aLclFlds[$value['COLUMN_NAME']]                                   = $this->localeSV($lString);
                $this->advCache['tableStructureLocales'][$dbName . '.' . $tblName] = $aLclFlds;
            }
        }
    }

    /**
     * Wrap the content into standard HTML based on predefined rules of standard actions
     *
     * @param array $contentArray
     * @param string $actionType
     * @return string
     */
    protected function incapsulateContentForPredefinedActions($contentArray, $actionType)
    {
        $sReturn = [];
        foreach ($contentArray as $lineNo => $contentLine) {
            $sReturn[] = '<div class="' . $this->inElmnts['Interface']['Gradients To Action'][$actionType]
                . ' rounded" style="padding: 5px;'
                . ($lineNo == 0 ? 'margin-bottom:10px' : '') . '">' . $contentLine . '</div>';
        }
        return implode('', $sReturn);
    }

    private function setPerformActions($action, $targetID, $targetTable, $listingQuery)
    {
        $sReturn = '';
        $this->connectToMySql($this->inElmnts['Database']);
        if (!defined('MYSQL_DATABASE')) {
            define('MYSQL_DATABASE', $this->inElmnts['Database']['database']);
        }
        $this->getTableTranslatedFields(MYSQL_DATABASE, $targetTable);
        switch ($action) {
            case 'add':
                $contentArray = [
                    $this->lclMsgCmn('i18n_NowYouAreInTheNewInformationAddingMode'),
                    $this->setViewModernAdd($targetTable, $targetID),
                ];
                $sReturn      = $this->incapsulateContentForPredefinedActions($contentArray, $action);
                break;
            case 'edit':
                $ftrs         = $this->handleMenuCommandsReadOnly($targetTable);
                $contentArray = [
                    $this->lclMsgCmn('i18n_NowYouAreInTheEditingModeOfExistingInformations'),
                    $this->setViewModernEdit($targetTable, $targetID, $ftrs),
                ];
                $sReturn      = $this->incapsulateContentForPredefinedActions($contentArray, $action);
                break;
            case 'delete':
                $sReturn      = $this->setViewModernDelete($targetTable, $targetID);
//                if ($this->mySQLconnection->affected_rows > 0) {
//                    if ($e[1] == 'ScheduleEventId') {
//                        $this->manageEventEventRemove();
//                        $e[1] = 'ScheduleId';
//                    }
//                    echo $this->handlePageReload('?view=list_' . $e[1], 2);
//                }
                break;
            case 'list':
                $sReturn      = $this->setStandardDynamicContent($targetID, $targetTable, $listingQuery);
                break;
        }
        return $sReturn;
    }

    private function setStandardDynamicContent($targetID, $targetTable, $listingQuery)
    {
        $sReturn   = [];
//        $this->loadIntoCacheTheActionDetails($allActions[$el]);
        $sReturn[] = '<div class="tabbertab" id="tabList" title="' . $this->localeSV('i18n_ValuesList') . '">'
//            . $this->handleDefaultValueForCertainPages()
            . $this->setViewModernListEnhanced($targetID, $targetTable, $listingQuery)
            . '</div><!-- from tabList -->';
        $sReturn[] = '<div class="tabbertab" id="tabDetails" title="' . $this->localeSV('i18n_ValuesDetails') . '">';
//        $btns       = explode(',', $this->appCache['actDtls'][$allActions[$el]]['Rights']);
//        if (in_array('add', $btns)) {
//            $sReturn[] = $this->setViewModernLinkAdd($el, $ftrs);
//        }
        $sReturn[] = '<div id="DynamicAddEditSpacer">&nbsp;</div>'
            . '</div><!-- from tabDetails -->';
        $tabName   = 'tabStandard' . $targetID;
        return $this->setJavascriptAddEditByAjax($tabName)
            . '<div class="tabber" id="' . $tabName . '">' . implode('', $sReturn) . '</div><!--from main tabber-->';
    }

    public function setViewModernAdd($tbl, $identifier, $ftrs = null)
    {
        $formFeatures = [
            'id'     => ('addForm' . date('YmdHis')),
            'method' => 'post',
            'action' => $_SERVER['PHP_SELF']
        ];
        if (@isset($ftrs['forceUpdate'])) {
            $formFeatures['insertAndUpdate'] = true;
        }
        if (isset($ftrs['hidden'])) {
            $formFeatures['hidden'] = $ftrs['hidden'];
        }
        if (isset($ftrs['readonly'])) {
            $formFeatures['readonly'] = $ftrs['readonly'];
        }
        $sForm = $this->setFormGenericSingleRecord($tbl, $formFeatures, [
            'view' => (isset($ftrs['forcedView']) ? $ftrs['forcedView'] : 'save_' . $identifier)
        ]);
        if (isset($ftrs['float_left'])) {
            $sReturn[] = $this->setStringIntoTag($sForm, 'div', ['style' => 'float:left;']);
        } else {
            $sReturn[] = $sForm;
        }
        $allowDisplay = true;
        if (isset($ftrs['restriction_authenticated'])) {
            if (!is_null($this->tCmnSession->get('new_username'))) {
                $this->tCmnSuperGlobals->set('restriction_authenticated', $this->tCmnSession->get('new_username'));
            } else {
                $allowDisplay = false;
            }
        }
        if ($allowDisplay) {
            if (isset($ftrs['additional_html'])) {
                $sReturn[] = $ftrs['additional_html'];
            }
            $finalReturn = implode('', $sReturn);
        } else {
            $lString     = [
                'Title' => $this->lclMsgCmn('i18n_ActionAdd_RestrictionTitle'),
                'Msg'   => $this->lclMsgCmn('i18n_ActionAdd_RestrictionDetails'),
            ];
            $finalReturn = $this->setFeedbackModern('error', $lString['Title'], $lString['Msg']);
        }
        return $finalReturn;
    }

    public function setViewModernEdit($tbl, $identifier, $ftrs = null)
    {
        if (!isset($ftrs['skip_reading_existing_values'])) {
            $this->getRowDataFromTable($tbl, [$identifier => $_REQUEST[$identifier]]);
        }
        if (isset($ftrs['inject_existing_values'])) {
            foreach ($ftrs['inject_existing_values'] as $key => $value) {
                $_REQUEST[$key] = $value;
            }
        }
        $allowDisplay = true;
        if (isset($ftrs['restriction_author'])) {
            if (is_null($_REQUEST[$ftrs['restriction_author']])) {
                $allowDisplay = false;
            } elseif ($_REQUEST[$ftrs['restriction_author']] != $this->tCmnSession->get('new_username')) {
                $allowDisplay = false;
            }
        }
        if ($allowDisplay) {
            $ftrs['forceUpdate'] = true;
            $sReturn             = $this->setViewModernAdd($tbl, $identifier, $ftrs);
        } else {
            echo $this->setFeedbackModern('error', 'SOX', 'Doar originatorul are drept de editare!');
            $sReturn = false;
        }
        return $sReturn;
    }

    private function setViewModernListEnhanced($targetID, $targetTable, $listingQuery, $ftrs = null)
    {
        $rights  = ['add', 'delete', 'edit', 'list'];
        $sReturn = [];
        if (isset($ftrs['noAddIcon'])) {
            // no Add Icon will be displayed
        } elseif ($rights != null) {
            if (in_array('add', $rights)) {
                $sReturn[] = str_replace(['view', '_'], ['action', '&amp;lang='
                    . $this->tCmnSession->get('lang') . '&amp;T=' . $targetTable
                    . '&amp;ID='], $this->setViewModernLinkAdd($targetID, null));
            }
            if (in_array('delete', $rights)) {
                $sReturn[] = $this->setJavascriptDeleteWithConfirmation();
            }
        }
        if (isset($ftrs['forcedQuery'])) {
            $query = $this->storedQuery($ftrs['forcedQuery']);
        } else {
            $query = $this->storedQuery('q' . $listingQuery);
        }
        if (isset($ftrs['query_match'])) {
            foreach ($ftrs['query_match'] as $key => $value) {
                $query = str_replace($key, $value, $query);
            }
        }
        if (!isset($ftrs['headers_breaked'])) {
            $ftrs['headers_breaked'] = false;
        }
        $ftrs['no_of_decimals'] = 0;
//        if (isset($ftrs['hidden_columns'])) {
//            $ftrs['hidden_columns'] = array_merge($ftrs['hidden_columns'], [
//                $this->appCache['actDtls'][$el]['MenuCommandDescription']
//            ]);
//        } else {
//            $ftrs['hidden_columns'] = [
//                $this->appCache['actDtls'][$el]['MenuCommandDescription'],
//                'InsertDateTime',
//                'ModificationDateTime'
//            ];
//        }
//        if (!is_null($this->appCache['actDtls'][$el]['Rights'])) {
//            $rights                 = explode(',', $this->appCache['actDtls'][$el]['Rights']);
//            $listingBtns            = ['delete', 'edit', 'schedule', 'list2'];
//            $btns                   = array_intersect($rights, $listingBtns);
//            $ftrs['actions']['key'] = 'view';
//            foreach ($btns as $value) {
//                $ftrs['actions'][$value] = [
//                    $value . '_' . $this->appCache['actDtls'][$el]['MenuCommandDescription'],
//                    [$this->appCache['actDtls'][$el]['MenuCommandDescription']]
//                ];
//            }
//        }
//        if (!isset($ftrs['IgnoreGrouping'])) {
//            if (!is_null($this->appCache['actDtls'][$el]['ListingGroupColumn'])) {
//                if (isset($ftrs['ListingGroupType'])) {
//                    $ftrs['grouping_cell_type'] = $ftrs['OverwriteGroupingType'];
//                } else {
//                    $ftrs['grouping_cell_type'] = $this->appCache['actDtls'][$el]['ListingGroupType'];
//                }
//                if (isset($ftrs['OverwriteGroupingCell'])) {
//                    $ftrs['grouping_cell'] = $ftrs['OverwriteGroupingCell'];
//                } else {
//                    $ftrs['grouping_cell'] = $this->appCache['actDtls'][$el]['ListingGroupColumn'];
//                }
//            }
//        }
        if (!isset($ftrs['noContentListing'])) {
            $dataArray = $this->setMySQLquery2Server($query, 'full_array_key_numbered')['result'];
            $ftrs      = array_merge($ftrs, ['showGroupingCounter' => 1]);
            $sReturn[] = $this->setArrayToTable($dataArray, $ftrs);
        }
        if (!isset($ftrs['noRecNoInfo'])) {
            $sReturn[] = $this->getFeedbackMySQLAffectedRecords();
        }
        return implode('', $sReturn);
    }

    /**
     * Place for all MySQL queries used within current class
     *
     * @param string $label
     * @param array $given_parameters
     * @return string
     */
    protected function storedQuery($label, $given_parameters = null)
    {
        $sReturn = call_user_func_array([$this, 'setRightQuery'], [$label, $given_parameters]);
        if ($sReturn === false) {
            echo $this->setFeedbackModern('error', 'No Query Found', '<b>' . $label . '</b> was not defined!');
        }
        return $sReturn;
    }
}
