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

    private function qInformationSourceList()
    {
        return 'SELECT '
            . implode(', ', [
                '`dis`.`InformationSourceID`',
                '`dis`.`InformationSourceName`',
            ])
            . 'FROM `dimension_information_source` `dis` '
            . 'GROUP BY `dis`.`InformationSourceName` '
            . 'ORDER BY `dis`.`InformationSourceName`;';
    }

    private function qDimensionMarketList()
    {
        return 'SELECT '
            . implode(', ', [
                '`dm`.`MarketID`',
                '`dm`.`MarketName`',
            ])
            . 'FROM `dimension_market` `dm` '
            . 'GROUP BY `dm`.`MarketName` '
            . 'ORDER BY `dm`.`MarketName`;';
    }

    private function qTargetScenarioList()
    {
        return 'SELECT '
            . implode(', ', [
                '`dts`.`TargetScenarioID`',
                '`dts`.`TargetScenarioName`',
                '`dts`.`TargetScenarioInStartTimestamp`',
                '`dts`.`TargetScenarioInLockTimestamp`',
            ])
            . 'FROM `dimension_target_scenario` `dts` '
            . 'GROUP BY `dts`.`TargetScenarioName` '
            . 'ORDER BY `dts`.`TargetScenarioName`;';
    }

    private function qTemplateTypeList()
    {
        return 'SELECT '
            . implode(', ', [
                '`tt`.`TemplateTypeID`' . $this->setFieldLocalized('template_type', 'TemplateTypeID'),
                '`tt`.`TemplateTypeName`' . $this->setFieldLocalized('template_type', 'TemplateTypeName'),
            ])
            . 'FROM `template_type` `tt` '
            . 'GROUP BY `tt`.`TemplateTypeName` '
            . 'ORDER BY `tt`.`TemplateTypeName`;';
    }

    private function qValidationFieldList()
    {
        return 'SELECT '
            . implode(', ', [
                '`vf`.`FieldID`',
                '`vf`.`FieldName`',
            ])
            . 'FROM `validation_field` `vf'
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
