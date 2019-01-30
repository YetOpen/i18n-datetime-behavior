<?php
/**
 * DateTimeI18NBehavior
 * Automatically converts date and datetime fields to I18N format
 *
 * @author Ricardo Grana <rickgrana@yahoo.com.br>, <ricardo.grana@pmm.am.gov.br>
 * @version 1.1
 *
 * @author Leo Zandvliet
 * @version 1.2
 * @release notes
 * 	- added afterSave() function which is needed in case of errors or further processing of model before end of application.
 *		Credits to Freezy (https://www.yiiframework.com/user/2129)
 *	- added public accessible functions to convert a specific attribute to locale or database notation
 *	- renamed local properties
 * 	- added properties for database notation
 * 	- added initFormats() function that will load default values from Yii::app()->locale and Yii::app()->format
 */
class DateTimeI18NBehavior extends CActiveRecordBehavior
{
	// Database format in php date time notation
	public $databaseDateFormat = null;
	public $databaseDateTimeFormat = null;
	
	// Database format in Yii date time notation
	public $databaseDateFormatYii = null;
	public $databaseDateTimeFormatYii = null;

	// Locale format in Yii date time notation
	public $localeDateFormat = null;
	public $localeDateTimeFormat = null;
	
	// Locale representation width in Yii textual notation
	public $localeDateWidth = null;
	public $localeDateTimeWidth = null;
	
	
	/**
	 * If format and width not set, retrieve them from the app and formatter.
	 */
	private function initFormats()
	{
		if($this->databaseDateFormat===null)
			$this->databaseDateFormat = 'Y-m-d';
		
		if($this->databaseDateTimeFormat===null)
			$this->databaseDateTimeFormat = 'Y-m-d H:i:s';
		
		// The Yii date format is slightly different
		if($this->databaseDateFormatYii===null)
			$this->databaseDateFormatYii = 'yyyy-MM-dd';
		
		// The Yii date time format is slightly different
		if($this->databaseDateTimeFormatYii===null)
			$this->databaseDateTimeFormatYii = 'yyyy-MM-dd hh:mm:ss';
		
		if($this->localeDateFormat===null)
			$this->localeDateFormat = Yii::app()->locale->getDateFormat(Yii::app()->format->dateFormat);
		
		if($this->localeDateTimeFormat===null)
			$this->localeDateTimeFormat = Yii::app()->format->datetimeFormat;
		
		if($this->localeDateWidth===null)
			$this->localeDateWidth = Yii::app()->format->dateFormat;
		
		if($this->localeDateTimeWidth===null)
			$this->localeDateTimeWidth = Yii::app()->format->timeFormat;
	}

	/**
	 * List of columns by model classes. Contains only date and datetime columns
	 * cache = array(
	 *  typeName => array(
	 *    'date' => array() // Columns with 'date' type
	 *    'datetime' => array() // Columns with 'datetime' type
	 *  )
	 * )
	 *
	 * @var array
	 * @see DateTimeI18NBehavior::checkCache
	 */
	private static $cache = array();

	public function afterConstruct($event)
	{
		$this->initFormats();
		return true;
	}
	
	public function beforeSave($event)
	{
		$this->convertToDatabaseFormat($event->sender);
		return true;
	}

	/**
	 * We must reconvert columns after they saved (little hack for CForm)
	 */
	public function afterSave($event)
	{
		$this->convertToLocaleFormat($event->sender);
		return true;
	}

	public function afterFind($event)
	{
		$this->convertToLocaleFormat($event->sender);
		return true;
	}

	public function toLocaleDate($attribute)
	{
		return Yii::app()->dateFormatter->formatDateTime(
					CDateTimeParser::parse($this->owner->$attribute, $this->databaseDateFormatYii),
					Yii::app()->format->dateFormat,
					null
				);
	}
	
	public function toLocaleDateTime($attribute)
	{
		return Yii::app()->dateFormatter->formatDateTime(
					CDateTimeParser::parse($this->owner->$attribute, $this->databaseDateTimeFormatYii),
					Yii::app()->format->dateFormat,
					Yii::app()->format->timeFormat
				);
	}
	
	public function toDatabaseDate($attribute)
	{
		return date(
					$this->databaseDateFormat,
					CDateTimeParser::parse($this->owner->$attribute, $this->localeDateFormat)
				);
	}
	
	public function toDatabaseDateTime($attribute)
	{
		return date(
					$this->databaseDateTimeFormat,
					CDateTimeParser::parse(
						$this->owner->$attribute,
						$this->localeDateTimeFormat
					)
				);
	}
	
	
	private function convertToLocaleFormat(CActiveRecord $model)
	{
		$this->checkCache($model);
		$type = get_class($model);
		$columns = &self::$cache[$type];
		
		// Convert all columns with 'date' type
		foreach ($columns['date'] as $columnName)
		{
			if (strlen($model->$columnName) > 0)
			{				
				$model->$columnName = $this->toLocaleDate($columnName);
			}
		}

		// Convert all columns with 'datetime' type
		foreach ($columns['datetime'] as $columnName)
		{			
			if (strlen($model->$columnName) > 0)
			{
				$model->$columnName = $this->toLocaleDateTime($columnName);
			}
		}
	}

	private function convertToDatabaseFormat(CActiveRecord $model)
	{
		$this->checkCache($model);
		$type = get_class($model);
		$columns = &self::$cache[$type];
		$this->initFormats();
		
		// Convert all columns with 'date' type
		foreach ($columns['date'] as $columnName)
		{
			if (strlen($model->$columnName) > 0)
			{
				$model->$columnName = $this->toDatabaseDate($columnName);
			}
		}

		// Convert all columns with 'datetime' type
		foreach ($columns['datetime'] as $columnName)
		{
			if (strlen($model->$columnName) > 0)
			{
				$model->$columnName = $this->toDatabaseDateTime($columnName);
			}
		}
	}
	
	/**
	 * Check cache for type of $model, and make if need
	 *
	 * @param CActiveRecord $model
	 */
	private function checkCache(CActiveRecord $model)
	{
		$type = get_class($model);
		if (!isset(self::$cache[$type]))
		{
			self::$cache[$type] = array(
				'date' => array(),
				'datetime' => array()
			);

			$columns = &self::$cache[$type];
			foreach ($model->tableSchema->columns as $columnName => $column)
			{
				if ($column->dbType == 'date' || $column->dbType == 'datetime')
					$columns[$column->dbType][] = $columnName;
			}
		}
	}
}
