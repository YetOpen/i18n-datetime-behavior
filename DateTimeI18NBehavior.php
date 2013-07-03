<?php

/*
 * DateTimeI18NBehavior
 * Automatically converts date and datetime fields to I18N format
 * 
 * Author: Ricardo Grana <rickgrana@yahoo.com.br>, <ricardo.grana@pmm.am.gov.br>
 * Version: 1.1
 * Requires: Yii 1.0.9 version 
 */

class DateTimeI18NBehavior  extends CActiveRecordBehavior
{
	public $dateOutcomeFormat = 'Y-m-d';
	public $dateTimeOutcomeFormat = 'Y-m-d H:i:s';

	public $dateIncomeFormat = 'yyyy-MM-dd';
	public $dateTimeIncomeFormat = 'yyyy-MM-dd hh:mm:ss';

	public function beforeSave($event){
		
		//search for date/datetime columns. Convert it to pure PHP date format
		foreach($event->sender->tableSchema->columns as $columnName => $column){
						
			if (($column->dbType != 'date') and ($column->dbType != 'datetime')) continue;
									
			if (!strlen($event->sender->$columnName)){ 
				$event->sender->$columnName = null;
				continue;
			}
			
			if (($column->dbType == 'date')) {				
				$event->sender->$columnName = date($this->dateOutcomeFormat, CDateTimeParser::parse($event->sender->$columnName, Yii::app()->locale->dateFormat));
			}else{
				
				$event->sender->$columnName = date($this->dateTimeOutcomeFormat, 
					CDateTimeParser::parse($event->sender->$columnName, 
						strtr(Yii::app()->locale->dateTimeFormat, 
							array("{0}" => Yii::app()->locale->timeFormat,
								  "{1}" => Yii::app()->locale->dateFormat))));
			}			
			
		}

		return true;
	}
	
	public function afterFind($event){
					
		foreach($event->sender->tableSchema->columns as $columnName => $column){
						
			if (($column->dbType != 'date') and ($column->dbType != 'datetime')) continue;
			
            // Store original somewhere
            if (isset($event->sender->_original_dates))
                $event->sender->_original_dates [$columnName] = $event->sender->$columnName;

			if (!strlen($event->sender->$columnName)){ 
				$event->sender->$columnName = null;
				continue;
			}
			
			if ($column->dbType == 'date'){				
				$event->sender->$columnName = Yii::app()->dateFormatter->formatDateTime(
								CDateTimeParser::parse($event->sender->$columnName, $this->dateIncomeFormat),'medium',null);
			}else{				
                $newval = CDateTimeParser::parse($event->sender->$columnName,$this->dateTimeIncomeFormat);
                // Check convert works, otherwise if source date is 0000-00-00 00:00:00 would return NOW()
				$event->sender->$columnName = $newval !== FALSE ? Yii::app()->dateFormatter->formatDateTime($newval, 'medium', 'medium') : null;
			}
		}
		return true;
	}
}
