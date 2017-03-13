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
/**
 * Author:  Daniel Popiniuc
 * Created: Mar 6, 2017
 */

CREATE DATABASE `structure_validated` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;

CREATE TABLE `table_dimension` (
    `TableDimensionID` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
    `TableNameDimension` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
    `TDCreationTimestamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'hidden',
    `TDModifiedTimestamp` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'hidden',
    PRIMARY KEY (`TableDimensionID`),
    UNIQUE KEY `UK_TableNameDimension` (`TableNameDimension`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `table_dimension` (`TableNameDimension`) VALUES
('dimension_information_source'),
('dimension_market'),
('dimension_target_scenario');

CREATE TABLE `table_measure_staging` (
    `TableMeasureStagingID` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
    `TableNameMeasureStaging` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
    `TMSCreationTimestamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'hidden',
    `TMSModifiedTimestamp` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'hidden',
    PRIMARY KEY (`TableMeasureStagingID`),
    UNIQUE KEY `UK_TableNameMeasureStaging` (`TableNameMeasureStaging`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `table_measure_reporting` (
    `TableMeasureReportingID` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
    `TableNameMeasureReporting` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
    `TMRCreationTimestamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'hidden',
    `TMRModifiedTimestamp` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'hidden',
    PRIMARY KEY (`TableMeasureReportingID`),
    UNIQUE KEY `UK_TableNameMeasureReporting` (`TableNameMeasureReporting`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `dimension_information_source` (
    `InformationSourceID` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
    `InformationSourceName` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
    `InformationSourceCreationTimestamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'hidden',
    `InformationSourceModifiedTimestamp` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'hidden',
    PRIMARY KEY (`InformationSourceID`),
    UNIQUE KEY `UK_InformationSourceName` (`InformationSourceName`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `dimension_information_source` (`InformationSourceName`) VALUES
('Primary Source of Information'),
('Secondary Source of Information');

CREATE TABLE `dimension_market` (
    `MarketID` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
    `MarketName` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
    `MarketCreationTimestamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'hidden',
    `MarketModifiedTimestamp` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'hidden',
    PRIMARY KEY (`MarketID`),
    UNIQUE KEY `UK_MarketName` (`MarketName`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `dimension_target_scenario` (
    `TargetScenarioID` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
    `TargetScenarioName` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
    `TargetScenarioInStartTimestamp` datetime NOT NULL,
    `TargetScenarioInLockTimestamp` datetime NOT NULL,
    `TargetScenarioCreationTimestamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'hidden',
    `TargetScenarioModifiedTimestamp` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'hidden',
    PRIMARY KEY (`TargetScenarioID`),
    UNIQUE KEY `UK_TargetScenarioName` (`TargetScenarioName`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `template_type` (
    `TemplateTypeID` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
    `TemplateTypeName` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
    `TableMeasureStagingID` tinyint(3) unsigned COLLATE utf8mb4_unicode_ci NOT NULL,
    `TableMeasureReportingID` tinyint(3) unsigned COLLATE utf8mb4_unicode_ci NOT NULL,
    `TemplateTypeCreationTimestamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'hidden',
    `TemplateTypeModifiedTimestamp` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'hidden',
    PRIMARY KEY (`TemplateTypeID`),
    UNIQUE KEY `UK_TemplateTypeName` (`TemplateTypeName`),
    UNIQUE KEY `UK_TableMeasureStagingID` (`TableMeasureStagingID`),
    UNIQUE KEY `UK_TableMeasureReportingID` (`TableMeasureReportingID`),
    CONSTRAINT `FK_tt_tms` FOREIGN KEY (`TableMeasureStagingID`) REFERENCES `table_measure_staging` (`TableMeasureStagingID`) ON UPDATE CASCADE,
    CONSTRAINT `FK_tt_tmr` FOREIGN KEY (`TableMeasureReportingID`) REFERENCES `table_measure_reporting` (`TableMeasureReportingID`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `template_loaded` (
    `TemplateLoadID` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
    `TemplateComment` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `TemplateLoadDate` date NOT NULL,
    `TemplateTypeID` tinyint(3) unsigned NOT NULL,
    `TargetScenarioID` tinyint(3) unsigned NOT NULL,
    `InformationSourceID` tinyint(3) unsigned NOT NULL,
    PRIMARY KEY (`TemplateLoadID`),
    UNIQUE KEY `UK_Template` (`TemplateTypeID`,`TemplateLoadDate`),
    UNIQUE KEY `UK_TemplateComment` (`TemplateComment`),
    KEY `K_TemplateTypeID` (`TemplateTypeID`),
    KEY `K_TargetScenarioID` (`TargetScenarioID`),
    KEY `K_InformationSourceID` (`InformationSourceID`),
    CONSTRAINT `FK_TemplateTypeID` FOREIGN KEY (`TemplateTypeID`) REFERENCES `template_type` (`TemplateTypeID`) ON UPDATE CASCADE,
    CONSTRAINT `FK_TargetScenarioID` FOREIGN KEY (`TargetScenarioID`) REFERENCES `dimension_target_scenario` (`TargetScenarioID`) ON UPDATE CASCADE,
    CONSTRAINT `FK_InformationSourceID` FOREIGN KEY (`InformationSourceID`) REFERENCES `dimension_information_source` (`InformationSourceID`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `validation_field` (
    `FieldID` smallint(6) NOT NULL AUTO_INCREMENT,
    `FieldName` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
    `FieldCreationTimestamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'hidden',
    `FieldModifiedTimestamp` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'hidden',
    PRIMARY KEY (`FieldID`),
    UNIQUE KEY `FieldName` (`FieldName`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `validation_rules` (
    `ValidationID` smallint(6) NOT NULL AUTO_INCREMENT,
    `TemplateType` tinyint(3) unsigned NOT NULL,
    `Field` tinyint(3) unsigned,
    `TargetTableName` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
    `TargetFieldName` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
    `BehaviourIfNotFound` enum('Fail','Add & Recheck') COLLATE utf8mb4_unicode_ci NOT NULL,
    `ValidationCreationTimestamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'hidden',
    `ValidationModifiedTimestamp` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'hidden',
    PRIMARY KEY (`ValidationID`),
    UNIQUE KEY `TemplateFieldName` (`TemplateFieldName`),
    KEY `K_Field` (`Field`),
    CONSTRAINT `FK_Field` FOREIGN KEY (`Field`) REFERENCES `validation_field` (`FieldID`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
