<?php

/*
 * The MIT License
 *
 * Copyright (c) 2017 Daniel Popiniuc
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

class SQLqueries
{

    use \danielgp\structure_validated\Basic;

    private function qDimensionMarketList()
    {
        return 'SELECT '
            . implode(', ', [
                '`dm`.`MarketID`',
                '`dm`.`MarketID`' . $this->setFieldLocalized('dimension_market', 'MarketID'),
                '`dm`.`MarketName`' . $this->setFieldLocalized('dimension_market', 'MarketName'),
            ])
            . 'FROM `dimension_market` `dm` '
            . 'GROUP BY `dm`.`MarketName` '
            . 'ORDER BY `dm`.`MarketName`;';
    }

    private function qDimensionInformationSourceList()
    {
        return 'SELECT '
            . implode(', ', [
                '`dis`.`InformationSourceID`',
                '`dis`.`InformationSourceID`'
                . $this->setFieldLocalized('dimension_information_source', 'InformationSourceID'),
                '`dis`.`InformationSourceName`'
                . $this->setFieldLocalized('dimension_information_source', 'InformationSourceName'),
            ])
            . 'FROM `dimension_information_source` `dis` '
            . 'GROUP BY `dis`.`InformationSourceName` '
            . 'ORDER BY `dis`.`InformationSourceName`;';
    }

    private function qDimensionTargetScenarioList()
    {
        return 'SELECT '
            . implode(', ', [
                '`dts`.`TargetScenarioID`',
                '`dts`.`TargetScenarioID`'
                . $this->setFieldLocalized('dimension_target_scenario', 'TargetScenarioID'),
                '`dts`.`TargetScenarioName`'
                . $this->setFieldLocalized('dimension_target_scenario', 'TargetScenarioName'),
                'REPLACE(REPLACE(REPLACE(CONCAT(DATE_FORMAT(`dts`.`TargetScenarioInStartTimestamp`, '
                . '"%W, %D %M %Y, %H:%i"), " --- ", DATE_FORMAT(`dts`.`TargetScenarioInLockTimestamp`, '
                . '"%W, %D %M %Y, %H:%i"))'
                . ', "st ", "<sup>st</sup> "), "nd ", "<sup>nd</sup> "), "th ", "<sup>th</sup> ")'
                . $this->setFieldLocalized('dimension_target_scenario', '~TargetScenarioEditingAllowedTimeRange'),
            ])
            . 'FROM `dimension_target_scenario` `dts` '
            . 'GROUP BY `dts`.`TargetScenarioName` '
            . 'ORDER BY `dts`.`TargetScenarioName`;';
    }

    private function qInternalTemplateLoadList()
    {
        return 'SELECT '
            . implode(', ', [
                '`tl`.`TemplateLoadID`',
                '`tl`.`TemplateLoadID`' . $this->setFieldLocalized('template_loaded', 'TemplateLoadID'),
                '`tl`.`TemplateComment`' . $this->setFieldLocalized('template_loaded', 'TemplateComment'),
                'REPLACE(REPLACE(REPLACE(`tl`.`TemplateLoadDate`, "%W, %D %M %Y, %H:%i")'
                . $this->setFieldLocalized('template_loaded', 'TemplateLoadDate'),
                '`tmr`.`TemplateTypeID`'
                . $this->setFieldLocalized('template_loaded', 'TemplateTypeID'),
            ])
            . 'FROM `template_loaded` `tl` '
            . 'INNER JOIN `table_measure_staging` `tms` '
            . 'ON `tt`.`TableMeasureStagingID` = `tms`.`TableMeasureStagingID` '
            . 'INNER JOIN `table_measure_reporting` `tmr` '
            . 'ON `tt`.`TableMeasureReportingID` = `tmr`.`TableMeasureReportingID` '
            . 'GROUP BY `tt`.`TemplateTypeName` '
            . 'ORDER BY `tt`.`TemplateTypeName`;';
    }

    private function qInternalTemplateTypeList()
    {
        return 'SELECT '
            . implode(', ', [
                '`tt`.`TemplateTypeID`',
                '`tt`.`TemplateTypeID`' . $this->setFieldLocalized('template_type', 'TemplateTypeID'),
                '`tt`.`TemplateTypeName`' . $this->setFieldLocalized('template_type', 'TemplateTypeName'),
                '`tms`.`TableNameMeasureStaging`' . $this->setFieldLocalized('template_type', 'TableMeasureStagingID'),
                '`tmr`.`TableNameMeasureReporting`'
                . $this->setFieldLocalized('template_type', 'TableMeasureReportingID'),
            ])
            . 'FROM `template_type` `tt` '
            . 'INNER JOIN `table_measure_staging` `tms` '
            . 'ON `tt`.`TableMeasureStagingID` = `tms`.`TableMeasureStagingID` '
            . 'INNER JOIN `table_measure_reporting` `tmr` '
            . 'ON `tt`.`TableMeasureReportingID` = `tmr`.`TableMeasureReportingID` '
            . 'GROUP BY `tt`.`TemplateTypeName` '
            . 'ORDER BY `tt`.`TemplateTypeName`;';
    }

    private function qInternalTableDimensionList()
    {
        return 'SELECT '
            . implode(', ', [
                '`td`.`TableDimensionID`',
                '`td`.`TableDimensionID`' . $this->setFieldLocalized('table_dimension', 'TableDimensionID'),
                '`td`.`TableNameDimension`' . $this->setFieldLocalized('table_dimension', 'TableNameDimension'),
            ])
            . 'FROM `table_dimension` `td` '
            . 'GROUP BY `td`.`TableNameDimension` '
            . 'ORDER BY `td`.`TableNameDimension`;';
    }

    private function qInternalTableMeasureStagingList()
    {
        return 'SELECT '
            . implode(', ', [
                '`tms`.`TableMeasureStagingID`',
                '`tms`.`TableMeasureStagingID`'
                . $this->setFieldLocalized('table_measure_staging', 'TableMeasureStagingID'),
                '`tms`.`TableNameMeasureStaging`'
                . $this->setFieldLocalized('table_measure_staging', 'TableNameMeasureStaging'),
            ])
            . 'FROM `table_measure_staging` `tms` '
            . 'GROUP BY `tms`.`TableNameMeasureStaging` '
            . 'ORDER BY `tms`.`TableNameMeasureStaging`;';
    }

    private function qInternalTableMeasureReportingList()
    {
        return 'SELECT '
            . implode(', ', [
                '`tmr`.`TableMeasureReportingID`',
                '`tmr`.`TableMeasureReportingID`'
                . $this->setFieldLocalized('table_measure_reporting', 'TableMeasureReportingID'),
                '`tmr`.`TableNameMeasureReporting`'
                . $this->setFieldLocalized('table_measure_reporting', 'TableNameMeasureReporting'),
            ])
            . 'FROM `table_measure_reporting` `tmr` '
            . 'GROUP BY `tmr`.`TableNameMeasureReporting` '
            . 'ORDER BY `tmr`.`TableNameMeasureReporting`;';
    }

    private function qValidationFieldList()
    {
        return 'SELECT '
            . implode(', ', [
                '`vf`.`FieldID`',
                '`vf`.`FieldID`' . $this->setFieldLocalized('validation_field', 'FieldID'),
                '`vf`.`FieldName`' . $this->setFieldLocalized('validation_field', 'FieldName'),
            ])
            . 'FROM `validation_field` `vf` '
            . 'GROUP BY `vf`.`FieldName` '
            . 'ORDER BY `vf`.`FieldName`;';
    }

    public function setRightQuery($label, $prmtrs = null)
    {
        if (method_exists($this, $label)) {
            $this->initializeSprGlbAndSession();
            if (is_null($prmtrs)) {
                return call_user_func([$this, $label]);
            }
            if (is_array($prmtrs)) {
                return call_user_func_array([$this, $label], [$prmtrs]);
            }
            return call_user_func([$this, $label], $prmtrs);
        }
        return false;
    }
}
