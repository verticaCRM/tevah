<?php
/*********************************************************************************
 * Copyright (C) 2011-2014 X2Engine Inc. All Rights Reserved.
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * X2Engine Inc. grants you a perpetual, non-exclusive, non-transferable license 
 * to install and use this Software for your internal business purposes.  
 * You shall not modify, distribute, license or sublicense the Software.
 * Title, ownership, and all intellectual property rights in the Software belong 
 * exclusively to X2Engine.
 * 
 * THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT WARRANTIES OF ANY KIND, EITHER 
 * EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION THE IMPLIED WARRANTIES OF 
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, TITLE, AND NON-INFRINGEMENT.
 ********************************************************************************/

/**
 * X2WebApplication class file.
 * 
 * X2WebApplication extends CWebApplication to provide additional functionality.
 * 
 * @package application.modules.contacts
 *
 */
class X2WebApplication extends CWebApplication {

	/**
	 * Creates a controller instance based on a route.
	 * Modified to check in /custom for controller files.
	 * See {@link CWebApplication::createController()} for details.
	 *
	 * @param string $route the route of the request.
	 * @param CWebModule $owner the module that the new controller will belong to. Defaults to null, meaning the application
	 * instance is the owner.
	 * @return array the controller instance and the action ID. Null if the controller class does not exist or the route is invalid.
	 */
	public function createController($route,$owner=null)
	{
		if($owner===null)
			$owner=$this;
		if(($route=trim($route,'/'))==='')
			$route=$owner->defaultController;
		$caseSensitive=$this->getUrlManager()->caseSensitive;

		$route.='/';
		while(($pos=strpos($route,'/'))!==false)
		{
			$id=substr($route,0,$pos);
			if(!preg_match('/^\w+$/',$id))
				return null;
			if(!$caseSensitive)
				$id=strtolower($id);
			$route=(string)substr($route,$pos+1);
			if(!isset($basePath))  // first segment
			{
				if(isset($owner->controllerMap[$id]))
				{
					return array(
						Yii::createComponent($owner->controllerMap[$id],$id,$owner===$this?null:$owner),
						$this->parseActionParams($route),
					);
				}

				if(($module=$owner->getModule($id))!==null) {
				
					// fix module's base paths in case module was loaded from /custom
					$module->basePath = Yii::resetCustomPath($module->basePath);
					$module->viewPath = Yii::resetCustomPath($module->viewPath);
					Yii::setPathOfAlias($module->getId(),$module->basePath);
				
					return $this->createController($route,$module);
				}
				$basePath=$owner->getControllerPath();
				$controllerID='';
			}
			else
				$controllerID.='/';
			$className=ucfirst($id).'Controller';

			$classFile=$basePath.DIRECTORY_SEPARATOR.$className.'.php';
			
			$extendedClassFile = Yii::getCustomPath($basePath.DIRECTORY_SEPARATOR.'My'.$className.'.php');
			
			if(is_file($extendedClassFile)) {					// see if there's an extended controller in /custom
				if(!class_exists($className,false))
					require(Yii::getCustomPath($classFile));	// import the 'real' controller
				$className = 'My'.$className;					// add "My" to the class name
				$classFile = $extendedClassFile;
			} else {
				$classFile = Yii::getCustomPath($classFile);	// look in /custom for controller file
			}

			if(is_file($classFile)) {
				if(!class_exists($className,false))
					require($classFile);
				if(class_exists($className,false) && is_subclass_of($className,'CController'))
				{
					$id[0]=strtolower($id[0]);
					return array(
						new $className($controllerID.$id,$owner===$this?null:$owner),
						$this->parseActionParams($route),
					);
				}
				return null;
			}
			$controllerID.=$id;
			$basePath.=DIRECTORY_SEPARATOR.$id;
		}
	}

}
