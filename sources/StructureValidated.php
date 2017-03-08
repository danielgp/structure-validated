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
    protected $appCache;

    public function __construct()
    {
        $this->inElmnts = $this->readTypeFromJsonFileStructureValidated('config', 'interfaceElements');
        $this->initializeSprGlbAndSession();
        $this->handleLocalizationStructureValidated($this->inElmnts['Application']);
        $action         = filter_var($this->tCmnSuperGlobals->get('action'), FILTER_SANITIZE_STRING);
        $targetID       = filter_var($this->tCmnSuperGlobals->get('ID'), FILTER_SANITIZE_STRING);
        $targetTable    = filter_var($this->tCmnSuperGlobals->get('T'), FILTER_SANITIZE_STRING);
        $listingQuery   = filter_var($this->tCmnSuperGlobals->get('Q'), FILTER_SANITIZE_STRING);
        if (in_array($action, ['add', 'edit', 'save'])) {
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
        foreach ($tableFields as $value) {
            $lString = 'i18n_MySQL_Field__' . $tblName . '|' . $value['COLUMN_NAME'];
            if ($this->localeSV($lString) != $lString) {
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

    /**
     * Cleans an array
     *
     * @param array $array2clean
     * return array
     */
    private function setCleanElement($array2clean)
    {
        $aReturn = [];
        foreach ($array2clean as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $key2 => $value2) {
                    $value[$key2] = addslashes($value2);
                }
            } else {
                $aReturn[$key] = addslashes($value);
            }
        }
        return $aReturn;
    }

    /**
     * Builds the query for insert or update
     *
     * @param string $tbl
     * @param array $fldVls
     * @param string $type
     * @param array $colsNQuot
     * @param $updtWhere
     * @return unknown_type
     */
    protected function setInsertUpdateQuery($tbl, $fldVls, $type, $colsNQuot = ['insertion_datetime'], $updtWhere = '')
    {
        $string2return  = '';
        $columns2ignore = ['PHPSESSID', 'insertAndUpdate'];
        $list_of_values = $this->setCleanElement($fldVls);
        switch ($type) {
            case 'insertAndUpdate':
                $fieldsPrimary  = array_keys($updtWhere);
            // this has been intentionally left blank
            case 'insert':
                $list_of_fields = '';
                $list_of_values = '';
                foreach ($fldVls as $key => $value) {
                    if (!in_array($key, $columns2ignore)) {
                        if ($list_of_fields != '') {
                            $list_of_fields .= ',';
                        }
                        $list_of_fields .= '`' . $key . '`';
                        if ($list_of_values != '') {
                            $list_of_values .= ',';
                        }
                        if (is_array($value)) {
                            $list_of_values .= ($key == 'UserPassword' ? '' : '"');
                            $list_of_values .= implode(',', $value);
                            $list_of_values .= ($key == 'UserPassword' ? '' : '"');
                        } else {
                            $list_of_values .= $value;
                        }
                        if (isset($fieldsPrimary)) {
                            if (!in_array($key, $fieldsPrimary)) {
                                $updateString[] = $key . ' = "' . $value . '" ';
                            }
                        }
                    }
                }
                $string2return = 'INSERT INTO `' . $tbl . '` (' . $list_of_fields . ') VALUES(' . $list_of_values . ')';
                if ($type == 'insertAndUpdate') {
                    $string2return .= ' ON DUPLICATE KEY UPDATE ' . implode(',', $updateString) . ';';
                } else {
                    $string2return .= ';';
                }
                break;
            case 'update':
                $list_of_fields_assigned = '';
                foreach ($fldVls as $key => $value) {
                    if (!in_array($key, $columns2ignore)) {
                        if (!in_array($key, array_keys($updtWhere))) {
                            if ($list_of_fields_assigned != '') {
                                $list_of_fields_assigned .= ',';
                            }
                            if (!in_array($key, $colsNQuot)) {
                                if (is_array($value)) {
                                    $list_of_fields_assigned .= '`' . $key . '` = "'
                                        . addslashes(implode(',', $value)) . '"';
                                } else {
                                    $list_of_fields_assigned .= '`' . $key . '` = "'
                                        . addslashes($value) . '"';
                                }
                            } else {
                                $list_of_fields_assigned .= $key . ' = '
                                    . addslashes($value);
                            }
                        }
                    }
                }
                $update_where_conditions = '';
                if (is_array($updtWhere)) {
                    foreach ($updtWhere as $key => $value) {
                        if ($update_where_conditions != '') {
                            $update_where_conditions .= ' AND ';
                        }
                        $update_where_conditions .= $key . ' = "' . $value . '"';
                    }
                }
                $string2return = 'UPDATE `' . $tbl . '` '
                    . 'SET ' . $list_of_fields_assigned . ' '
                    . 'WHERE ' . $update_where_conditions . ';';
                break;
        }
        return str_replace('"NULL"', 'NULL', $string2return);
    }

    /**
     * Builds up a confirmation dialog and return delection if Yes
     *
     * @return string
     */
    protected function setJavascriptDeleteWithConfirmationSV()
    {
        return $this->setJavascriptContent('function setQuest(a, b) { '
                . 'c = a.indexOf("_"); switch(a.slice(0, c)) { '
                . 'case \'delete\': '
                . 'if (confirm(\'' . $this->lclMsgCmn('i18n_ActionDelete_ConfirmationQuestion') . '\')) { '
                . 'window.location = document.location.protocol + "//" + '
                . 'document.location.host + document.location.pathname + '
                . '"?action=" + a + "&" + b; } break; } }');
    }

    private function setPerformActions($action, $targetID, $targetTable, $listingQuery)
    {
        $sReturn = '';
        $this->connectToMySql($this->inElmnts['Database']);
        if (!defined('MYSQL_DATABASE')) {
            define('MYSQL_DATABASE', $this->inElmnts['Database']['database']);
        }
        $this->getTableTranslatedFields(MYSQL_DATABASE, $targetTable);
        $urlParts = [
            'ID=' . $targetID,
            'T=' . $targetTable,
            'Q=' . $listingQuery
        ];
        switch ($action) {
            case 'add':
                $contentArray = [
                    $this->lclMsgCmn('i18n_NowYouAreInTheNewInformationAddingMode'),
                    $this->setViewModernAdd($targetTable, $targetID),
                ];
                $sReturn      = $this->incapsulateContentForPredefinedActions($contentArray, $action);
                break;
            case 'edit':
                $contentArray = [
                    $this->lclMsgCmn('i18n_NowYouAreInTheEditingModeOfExistingInformations'),
                    $this->setViewModernEdit($targetTable, $targetID, null),
                ];
                $sReturn      = $this->incapsulateContentForPredefinedActions($contentArray, $action);
                break;
            case 'delete':
                $sReturn      = $this->setViewModernDelete($targetTable, $targetID);
                if ($this->mySQLconnection->affected_rows > 0) {
                    echo $this->handlePageReload('?action=list&amp;' . implode('&amp;', $urlParts), 2);
                }
                break;
            case 'list':
                $sReturn         = $this->setStandardDynamicContent($targetID, $targetTable, $listingQuery);
                break;
            case 'save':
                $finalJavascript = $this->setJavascriptContent(implode('', [
                    '$("#SaveFeedback").fadeOut(1900, function() {',
                    '$(this).remove();',
                    '});',
                ]));
                $sReturn         = $this->setViewModernSave($targetTable, $targetID, null)
                    . '<div id="SaveFeedback">' . $this->appCache['saveFeedback'] . '</div>'
                    . $finalJavascript;
                if ($this->mySQLconnection->affected_rows > 0) {
                    $sReturn .= $this->handlePageReload('?action=list&amp;' . implode('&amp;', $urlParts), 2);
                } else {
                    $sReturn .= '<p style="color:red;background:#fff;">MySQL error: '
                        . $this->mySQLconnection->errno . ' meaning ' . $this->mySQLconnection->error
                        . '<br/>The query tried was: ' . $this->appCache['saveQuery'] . '</p>';
                }
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
        $formFeatures = array_merge($this->setViewSanitizeFormFeatures($ftrs, ['hidden', 'readonly']), [
            'id'     => ('addForm' . date('YmdHis')),
            'method' => 'post',
            'action' => $this->tCmnSuperGlobals->getScriptName()
        ]);
        $sReturn      = null;
        $sReturn[]    = $this->setFormGenericSingleRecord($tbl, $formFeatures, [
            'action' => 'save',
            'ID'     => $identifier,
            'T'      => filter_var($this->tCmnSuperGlobals->get('T'), FILTER_SANITIZE_STRING),
            'Q'      => filter_var($this->tCmnSuperGlobals->get('Q'), FILTER_SANITIZE_STRING),
        ]);
        if (isset($ftrs['additional_html'])) {
            $sReturn[] = $ftrs['additional_html'];
        }
        return implode('', $sReturn);
    }

    public function setViewModernEdit($tbl, $identifier, $ftrs = null)
    {
        if (!isset($ftrs['skip_reading_existing_values'])) {
            $this->getRowDataFromTable($tbl, [
                $identifier => filter_var($this->tCmnSuperGlobals->get('PKvalue'), FILTER_SANITIZE_STRING)
            ]);
        }
        if (isset($ftrs['inject_existing_values'])) {
            foreach ($ftrs['inject_existing_values'] as $key => $value) {
                $this->tCmnRequest->request->set($key, $value);
            }
        }
        return $this->setViewModernAdd($tbl, $identifier, $ftrs);
    }

    protected function setViewModernLinkAddSV($identifier, $ftrs = null)
    {
        $btnText     = '<i class="fa fa-plus-square">&nbsp;</i>' . '&nbsp;' . $this->lclMsgCmn('i18n_AddNewRecord');
        $tagFeatures = [
            'href'  => $this->setViewModernLinkAddUrlSV($identifier, $ftrs),
            'style' => 'margin: 5px 0px 10px 0px; display: inline-block;',
        ];
        return $this->setStringIntoTag($btnText, 'a', $tagFeatures);
    }

    protected function setViewModernLinkAddInjectedArgumentsSV($ftrs = null)
    {
        $sArgmnts = '';
        if (isset($ftrs['injectAddArguments'])) {
            foreach ($ftrs['injectAddArguments'] as $key => $value) {
                $sArgmnts .= '&amp;' . $key . '=' . $value;
            }
        }
        return $sArgmnts;
    }

    protected function setViewModernLinkAddUrlSV($identifier, $ftrs = null)
    {
        $sArgmnts  = $this->setViewModernLinkAddInjectedArgumentsSV($ftrs);
        $this->initializeSprGlbAndSession();
        $addingUrl = $this->tCmnSuperGlobals->getScriptName() . '?action=add&amp;ID=' . $identifier . $sArgmnts;
        if (!isset($ftrs['NoAjax'])) {
            $addingUrl = 'javascript:loadAE(\'' . $addingUrl . '\');';
        }
        return $addingUrl;
    }

    private function setViewModernListEnhanced($targetID, $targetTable, $listingQuery, $ftrs = null)
    {
        $rights  = ['add', 'delete', 'edit', 'list'];
        $sReturn = [];
        if (isset($ftrs['noAddIcon'])) {
            // no Add Icon will be displayed
        } elseif ($rights != null) {
            if (in_array('add', $rights)) {
                $sReturn[] = $this->setViewModernLinkAddSV($targetID, [
                    'injectAddArguments' => [
                        'land' => $this->tCmnSession->get('lang'),
                        'T'    => $targetTable,
                        'Q'    => $listingQuery,
                    ]
                ]);
            }
            if (in_array('delete', $rights)) {
                $sReturn[] = $this->setJavascriptDeleteWithConfirmationSV();
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
        if (isset($ftrs['hidden_columns'])) {
            $ftrs['hidden_columns'] = array_merge($ftrs['hidden_columns'], [
                $this->advCache['tableStructureLocales'][MYSQL_DATABASE . '.' . $targetTable][$targetID]
            ]);
        } else {
            $ftrs['hidden_columns'] = [
                $this->advCache['tableStructureLocales'][MYSQL_DATABASE . '.' . $targetTable][$targetID]
            ];
        }
//        if (!is_null($this->appCache['actDtls'][$el]['Rights'])) {
//            $rights                 = explode(',', $this->appCache['actDtls'][$el]['Rights']);
        $listingBtns            = ['delete', 'edit'];
        $btns                   = array_intersect($rights, $listingBtns);
        $ftrs['actions']['key'] = 'view';
        foreach ($btns as $value) {
            switch ($value) {
                case 'delete':
                    $ftrs['actions'][$value] = [
                        'value',
                        $targetID
                    ];
                    break;
                default:
                    $ftrs['actions'][$value] = [
                        $value . '' . $this->appCache['actDtls'][$targetID]['ID'],
                        [$this->appCache['actDtls'][$targetID]['ID']]
                    ];
                    break;
            }
        }
//        }
        if (!isset($ftrs['noContentListing'])) {
            $dataArray = $this->setMySQLquery2Server($query, 'full_array_key_numbered')['result'];
            $ftrs      = array_merge($ftrs, ['showGroupingCounter' => 1]);
            $sReturn[] = $this->setArrayToTable($dataArray, $ftrs);
        }
//        if (!isset($ftrs['noRecNoInfo'])) {
//            $sReturn[] = $this->getFeedbackMySQLAffectedRecords();
//        }
        return implode('', $sReturn);
    }

    public function setViewModernSave($tbl, $identifier, $ftrs = null)
    {
        $array2save          = $_REQUEST;
        $elementsToEliminate = [
            'specialNoHeader',
            'specialNoMenu',
            'specialNoTitle',
            'specialNoFooter',
            'action',
            'ID',
            'T',
            'Q',
        ];
        foreach ($array2save as $key => $value) {
            if (in_array($key, $elementsToEliminate)) {
                unset($array2save[$key]);
            }
        }
        $forceInsert = false;
        if (isset($ftrs['insertAndUpdate'])) {
            $forceInsert = true;
        }
        $lString['Title'] = $this->lclMsgCmn('i18n_Action_Confirmation');
        if ($forceInsert) {
            $qry                             = $this->setInsertUpdateQuery($tbl, $array2save, 'insertAndUpdate', [''], [
                $identifier => $_REQUEST[$identifier]
            ]);
            $this->appCache['saveQuery']     = $qry;
            $this->appCache['saveQueryType'] = 'DynamicForceInsert';
            $this->setMySQLquery2Server($qry);
            if ($this->mySQLconnection->affected_rows > 0) {
                $lString['Tp']         = 'check';
                $lString['Msg']        = $this->lclMsgCmn('i18n_Action_Successful');
                $_REQUEST[$identifier] = $this->mySQLconnection->insert_id;
            } else {
                $lString['Tp']  = 'error';
                $lString['Msg'] = $this->lclMsgCmn('i18n_Action_Failed');
            }
        } elseif (isset($_REQUEST[$identifier])) {
            $qry                             = $this->setInsertUpdateQuery($tbl, $array2save, 'update', [''], [
                $identifier => $_REQUEST[$identifier]
            ]);
            $this->appCache['saveQuery']     = $qry;
            $this->appCache['saveQueryType'] = 'DynamicUpdate';
            $this->setMySQLquery2Server($qry);
            if ($this->mySQLconnection->affected_rows > 0) {
                $lString['Tp']  = 'check';
                $lString['Msg'] = $this->lclMsgCmn('i18n_ActionUpdate_Successful');
            } else {
                $lString['Tp']  = 'error';
                $lString['Msg'] = $this->lclMsgCmn('i18n_ActionUpdate_Failed');
            }
        } else {
            $qry                             = $this->setInsertUpdateQuery($tbl, $array2save, 'insert');
            $this->appCache['saveQuery']     = $qry;
            $this->appCache['saveQueryType'] = 'DynamicInsert';
            $this->setMySQLquery2Server($qry);
            if ($this->mySQLconnection->affected_rows > 0) {
                $lString['Tp']         = 'check';
                $lString['Msg']        = $this->lclMsgCmn('i18n_ActionAdd_Successful');
                $_REQUEST[$identifier] = $this->mySQLconnection->insert_id;
            } else {
                $lString['Tp']  = 'error';
                $lString['Msg'] = $this->lclMsgCmn('i18n_ActionAdd_Failed');
            }
        }
        $this->appCache['saveFeedback'] = $this->setFeedbackModern($lString['Tp'], $lString['Title'], $lString['Msg']);
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
