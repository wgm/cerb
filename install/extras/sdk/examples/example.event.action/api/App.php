<?php
class ExampleEventAction_ExampleAction extends Extension_DevblocksEventAction {
	const ID = 'exampleeventaction.action';
	
	function render(Extension_DevblocksEvent $event, Model_TriggerEvent $trigger, $params=array(), $seq=null) {
		$tpl = DevblocksPlatform::services()->template();
		$tpl->assign('params', $params);

		if(!is_null($seq))
			$tpl->assign('namePrefix','action'.$seq);
			
		$tpl->assign('token_labels', $event->getLabels($trigger));
		
		$tpl->display('devblocks:example.event.action::config.tpl');
	}
	
	function simulate($token, Model_TriggerEvent $trigger, $params, DevblocksDictionaryDelegate $dict) {
		
	}
	
	function run($token, Model_TriggerEvent $trigger, $params, DevblocksDictionaryDelegate $dict) {
		// [TODO] Do something with the $params and $values

		//$tpl_builder = DevblocksPlatform::services()->templateBuilder();
		//$content = $tpl_builder->build($params['value'], $values);
		//var_dump($content);
	}
};