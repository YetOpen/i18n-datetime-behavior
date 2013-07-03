i18n-datetime-behavior
======================

Yii i18n-datetime-behavior extension
Sourced from version 1.1 of http://www.yiiframework.com/extension/i18n-datetime-behavior/

Author: Ricardo Grana <rickgrana@yahoo.com.br>, <ricardo.grana@pmm.am.gov.br>


This extension is a behavior that can be used in models to allow them to automatically parse and format i18N date formats.

The behavior scans for date and datetime fields in the model attributes, and do the conversions needed.

Documentation 
=============

Requirements 
------------

Yii 1.0.9 or above
You need to have defined your desired language at the entry script.
Installation 

Extract the release file and put it under protected/extensions

Usage 
-----

In you model, add the following code:
```php
public function behaviors()
{
    return array('datetimeI18NBehavior' => array('class' => 'ext.DateTimeI18NBehavior')); // 'ext' is in Yii 1.0.8 version. For early versions, use 'application.extensions' instead.
}
```
IMPORTANT! This behavior changes afterFind and beforeSave events. If your model have to have coding, please, don't forget to add the parent reference:
```php
protected function beforeSave(){
    if (!parent::beforeSave()) return false;
    ....your code
}
```
The same for afterFind method. Otherwise, this behavior will not make effect.

