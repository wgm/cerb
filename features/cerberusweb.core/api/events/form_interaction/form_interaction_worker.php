<?php
/***********************************************************************
| Cerb(tm) developed by Webgroup Media, LLC.
|-----------------------------------------------------------------------
| All source code & content (c) Copyright 2002-2019, Webgroup Media LLC
|   unless specifically noted otherwise.
|
| This source code is released under the Devblocks Public License.
| The latest version of this license can be found here:
| http://cerb.ai/license
|
| By using this software, you acknowledge having read this license
| and agree to be bound thereby.
| ______________________________________________________________________
|	http://cerb.ai	    http://webgroup.media
***********************************************************************/

class Event_FormInteractionWorker extends Extension_DevblocksEvent {
	const ID = 'event.form.interaction.worker';
	
	/*
	function renderEventParams(Model_TriggerEvent $trigger=null) {
		$tpl = DevblocksPlatform::services()->template();
		$tpl->assign('trigger', $trigger);
		/$tpl->display('devblocks:cerberusweb.core::events/.../params.tpl');
	}
	*/
	
	/**
	 *
	 * @param Model_TriggerEvent $trigger
	 * @return Model_DevblocksEvent
	 */
	function generateSampleEventModel(Model_TriggerEvent $trigger) {
		$active_worker = CerberusApplication::getActiveWorker();
		$actions = [];
		
		return new Model_DevblocksEvent(
			self::ID,
			[
				'actions' => &$actions,
				
				'client_browser' => null,
				'client_browser_version' => null,
				'client_ip' => null,
				'client_platform' => null,
				
				'worker_id' => $active_worker ? $active_worker->id : 0,
			]
		);
	}
	
	function setEvent(Model_DevblocksEvent $event_model=null, Model_TriggerEvent $trigger=null) {
		$labels = [];
		$values = [];
		
		/**
		 * Behavior
		 */
		
		$merge_labels = $merge_values = [];
		CerberusContexts::getContext(CerberusContexts::CONTEXT_BEHAVIOR, $trigger, $merge_labels, $merge_values, null, true);

			// Merge
			CerberusContexts::merge(
				'behavior_',
				'',
				$merge_labels,
				$merge_values,
				$labels,
				$values
			);
			
		/**
		 * Worker
		 */
		
		@$worker_id = $event_model->params['worker_id'];
		$merge_labels = $merge_values = [];
		CerberusContexts::getContext(CerberusContexts::CONTEXT_WORKER, $worker_id, $merge_labels, $merge_values, null, true);

			// Merge
			CerberusContexts::merge(
				'worker_',
				'',
				$merge_labels,
				$merge_values,
				$labels,
				$values
			);
			
		if($event_model) {
			// Actions
			if($event_model && array_key_exists('actions', $event_model->params)) {
				$values['_actions'] =& $event_model->params['actions'];
			} else {
				$values['_actions'] = [];
			}
			
			// Client
			@$client_browser = $event_model->params['client_browser'];
			@$client_browser_version = $event_model->params['client_browser_version'];
			@$client_ip = $event_model->params['client_ip'];
			@$client_platform = $event_model->params['client_platform'];
			
			$values['client_browser'] = $client_browser;
			$values['client_browser_version'] = $client_browser_version;
			$values['client_ip'] = $client_ip;
			$values['client_platform'] = $client_platform;
			
		} else {
			$values['_actions'] = [];
		}
		
		$labels['client_browser'] = 'Client Browser';
		$labels['client_browser_version'] = 'Client Browser Version';
		$labels['client_ip'] = 'Client IP';
		$labels['client_platform'] = 'Client Platform';
		
		/**
		 * Return
		 */

		$this->setLabels($labels);
		$this->setValues($values);
	}
	
	function getValuesContexts($trigger) {
		$vals = array(
			'behavior_id' => array(
				'label' => 'Behavior',
				'context' => CerberusContexts::CONTEXT_BEHAVIOR,
			),
			'behavior_bot_id' => array(
				'label' => 'Bot',
				'context' => CerberusContexts::CONTEXT_BOT,
			),
			'worker_id' => array(
				'label' => 'Worker',
				'context' => CerberusContexts::CONTEXT_WORKER,
			),
		);
		
		$vars = parent::getValuesContexts($trigger);
		
		$vals_to_ctx = array_merge($vals, $vars);
		DevblocksPlatform::sortObjects($vals_to_ctx, '[label]');
		
		return $vals_to_ctx;
	}
	
	function getConditionExtensions(Model_TriggerEvent $trigger) {
		$labels = $this->getLabels($trigger);
		$types = $this->getTypes();
		
		// Client
		$labels['client_browser'] = 'Client Browser';
		$labels['client_browser_version'] = 'Client Browser Version';
		$labels['client_ip'] = 'Client IP';
		$labels['client_platform'] = 'Client Platform';
		
		$types['client_browser'] = Model_CustomField::TYPE_SINGLE_LINE;
		$types['client_browser_version'] = Model_CustomField::TYPE_SINGLE_LINE;
		$types['client_ip'] = Model_CustomField::TYPE_SINGLE_LINE;
		$types['client_platform'] = Model_CustomField::TYPE_SINGLE_LINE;

		$conditions = $this->_importLabelsTypesAsConditions($labels, $types);
		
		return $conditions;
	}
	
	function renderConditionExtension($token, $as_token, $trigger, $params=[], $seq=null) {
		$tpl = DevblocksPlatform::services()->template();
		$tpl->assign('params', $params);

		if(!is_null($seq))
			$tpl->assign('namePrefix','condition'.$seq);
		
		switch($as_token) {
		}

		$tpl->clearAssign('namePrefix');
		$tpl->clearAssign('params');
	}
	
	function runConditionExtension($token, $as_token, $trigger, $params, DevblocksDictionaryDelegate $dict) {
		$pass = true;
		
		switch($as_token) {
			default:
				$pass = false;
				break;
		}
		
		return $pass;
	}
	
	function getActionExtensions(Model_TriggerEvent $trigger) {
		$actions =
			[
				'prompt_captcha' => [
					'label' => 'Prompt with CAPTCHA challenge',
					'notes' => '',
					'params' => [
						'var' => [
							'type' => 'placeholder',
							'required' => true,
							'notes' => 'The placeholder to set with the CAPTCHA challenge response',
						],
					],
				],
				'prompt_checkboxes' => [
					'label' => 'Prompt with multiple choices',
					'notes' => '',
					'params' => [
						'label' => [
							'type' => 'text',
							'required' => true,
							'notes' => 'The label for the set of choices',
						],
						'options' => [
							'label' => [
								'type' => 'text',
								'required' => true,
								'notes' => 'Predefined options separated by newlines',
							],
						],
						'var' => [
							'type' => 'placeholder',
							'required' => true,
							'notes' => "The placeholder to set with the user's choices",
						],
						'var_validate' => [
							'type' => 'text',
							'notes' => "A template for validating this prompt",
						],
					],
				],
				'prompt_chooser' => [
					'label' => 'Prompt with record chooser',
					'notes' => '',
					'params' => [
						'label' => [
							'type' => 'text',
							'required' => true,
							'notes' => 'The label for the set of choices',
						],
					],
				],
				'prompt_files' => [
					'label' => 'Prompt with file upload',
					'notes' => '',
					'params' => [
						'label' => [
							'type' => 'text',
							'required' => true,
							'notes' => 'The label for the set of choices',
						],
					],
				],
				'prompt_radios' => [
					'label' => 'Prompt with single choice',
					'notes' => '',
					'params' => [
						'label' => [
							'type' => 'text',
							'required' => true,
							'notes' => 'The label for the set of choices',
						],
						'style' => [
							'label' => [
								'type' => 'text',
								'notes' => '`radios` or `buttons`',
							],
						],
						'orientation' => [
							'label' => [
								'type' => 'text',
								'notes' => '`horizontal` or `vertical`',
							],
						],
						'options' => [
							'label' => [
								'type' => 'text',
								'required' => true,
								'notes' => 'Predefined options separated by newlines',
							],
						],
						'default' => [
							'label' => [
								'type' => 'text',
								'notes' => 'The selected option by default',
							],
						],
						'var' => [
							'type' => 'placeholder',
							'required' => true,
							'notes' => "The placeholder to set with the user's choices",
						],
						'var_format' => [
							'type' => 'text',
							'notes' => "A template for formatting this prompt",
						],
						'var_validate' => [
							'type' => 'text',
							'notes' => "A template for validating this prompt",
						],
					],
				],
				'prompt_text' => [
					'label' => 'Prompt with text',
					'notes' => '',
					'params' => [
						'label' => [
							'type' => 'text',
							'required' => true,
							'notes' => 'The label for the text input',
						],
						'placeholder' => [
							'type' => 'text',
							'notes' => 'The descriptive text in the textbox when empty',
						],
						'default' => [
							'type' => 'text',
							'notes' => 'The default value in the textbox',
						],
						'mode' => [
							'type' => 'text',
							'notes' => '`multiple` (multiple lines), or omit for single line',
						],
						'var' => [
							'type' => 'placeholder',
							'required' => true,
							'notes' => "The placeholder to set with the user's input",
						],
						'var_format' => [
							'type' => 'text',
							'notes' => "A template for formatting this prompt",
						],
						'var_validate' => [
							'type' => 'text',
							'notes' => "A template for validating this prompt",
						],
					],
				],
				'prompt_submit' => [
					'label' => 'Prompt with submit',
					'notes' => 'This action has no configurable parameters.',
					'params' => [],
				],
				'respond_sheet' => [
					'label' => 'Respond with sheet',
					'notes' => '',
					'params' => [
						'data_query' => [
							'type' => 'text',
							'required' => true,
							'notes' => "The [data query](/docs/data-queries/) to run",
						],
						'placeholder_simulator_yaml' => [
							'type' => 'yaml',
							'notes' => "The test placeholder values when using the simulator",
						],
						'sheet_yaml' => [
							'type' => 'yaml',
							'required' => true,
							'notes' => "The [sheet](/docs/sheets/) schema to display",
						],
					],
				],
				'respond_text' => [
					'label' => 'Respond with text',
					'notes' => '',
					'params' => [
						'message' => [
							'type' => 'text',
							'required' => true,
							'notes' => "The message to send to the user",
						],
						'format' => [
							'type' => 'text',
							'notes' => "The format of the message: `markdown`, `html`, or omit for plaintext",
						],
					],
				],
			]
			;
		
		return $actions;
	}
	
	function getActionDefaultOn() {
		return 'worker_id';
	}
	
	function renderActionExtension($token, $trigger, $params=[], $seq=null) {
		$tpl = DevblocksPlatform::services()->template();
		
		if(!is_array($params))
			$params = [];
		
		$tpl->assign('params', $params);

		if(!is_null($seq))
			$tpl->assign('namePrefix','action'.$seq);

		$labels = $this->getLabels($trigger);
		$tpl->assign('token_labels', $labels);
			
		switch($token) {
			case 'prompt_captcha':
				$tpl->display('devblocks:cerberusweb.core::events/form_interaction/_common/prompts/action_prompt_captcha.tpl');
				break;
				
			case 'prompt_checkboxes':
				$tpl->display('devblocks:cerberusweb.core::events/form_interaction/_common/prompts/action_prompt_checkboxes.tpl');
				break;
				
			case 'prompt_chooser':
				$record_contexts = Extension_DevblocksContext::getAll(false);
				$tpl->assign('record_contexts', $record_contexts);
				
				$tpl->display('devblocks:cerberusweb.core::events/form_interaction/_common/prompts/action_prompt_chooser.tpl');
				break;
				
			case 'prompt_files':
				$tpl->display('devblocks:cerberusweb.core::events/form_interaction/_common/prompts/action_prompt_files.tpl');
				break;
				
			case 'prompt_radios':
				$tpl->display('devblocks:cerberusweb.core::events/form_interaction/_common/prompts/action_prompt_radios.tpl');
				break;
				
			case 'prompt_text':
				$tpl->display('devblocks:cerberusweb.core::events/form_interaction/_common/prompts/action_prompt_text.tpl');
				break;
				
			case 'prompt_submit':
				$tpl->display('devblocks:cerberusweb.core::events/form_interaction/_common/action_prompt_submit.tpl');
				break;
			
			case 'respond_text':
				$tpl->display('devblocks:cerberusweb.core::events/form_interaction/_common/responses/action_respond_text.tpl');
				break;
				
			case 'respond_sheet':
				if(!array_key_exists('data_query', $params))
					$params['data_query'] = "type:worklist.records\nof:tickets\nquery.required:(\n)\nquery:(\n)\nexpand:[custom_]\nformat:dictionaries";
					
				if(!array_key_exists('placeholder_simulator_yaml', $params))
					$params['placeholder_simulator_yaml'] = "# key: value\n";
				
				if(!array_key_exists('sheet_yaml', $params))
					$params['sheet_yaml'] = "layout:\n  style: table # [table,fieldsets]\n  headings: true\n  paging: true\n  #title_column: _label\ncolumns:\n- card:\n    key: _label\n    label: Name";
				
				$tpl->assign('params', $params);
				$tpl->display('devblocks:cerberusweb.core::events/form_interaction/_common/responses/action_respond_sheet.tpl');
				break;
		}
		
		$tpl->clearAssign('params');
		$tpl->clearAssign('namePrefix');
		$tpl->clearAssign('token_labels');
	}
	
	function simulateActionExtension($token, $trigger, $params, DevblocksDictionaryDelegate $dict) {
		$out = '';
		
		switch($token) {
			case 'prompt_captcha':
				$out = ">>> Prompting with CAPTCHA challenge\n";
				break;
				
			case 'prompt_checkboxes':
				$tpl_builder = DevblocksPlatform::services()->templateBuilder();
				$label = $tpl_builder->build($params['label'], $dict);
				$options = $tpl_builder->build($params['options'], $dict);
				
				$out = sprintf(">>> Prompting with checkboxes\nLabel: %s\nOptions: %s\n",
					$label,
					$options
				);
				break;
				
			case 'prompt_chooser':
				$tpl_builder = DevblocksPlatform::services()->templateBuilder();
				$label = $tpl_builder->build($params['label'], $dict);
				$options = $tpl_builder->build($params['options'], $dict);
				
				$out = sprintf(">>> Prompting with record chooser\nLabel: %s\nOptions: %s\n",
					$label,
					$options
				);
				break;
				
			case 'prompt_files':
				$tpl_builder = DevblocksPlatform::services()->templateBuilder();
				$label = $tpl_builder->build($params['label'], $dict);
				$options = $tpl_builder->build($params['options'], $dict);
				
				$out = sprintf(">>> Prompting with file upload\nLabel: %s\nOptions: %s\n",
					$label,
					$options
				);
				break;
				
			case 'prompt_radios':
				$tpl_builder = DevblocksPlatform::services()->templateBuilder();
				$label = $tpl_builder->build($params['label'], $dict);
				$options = $tpl_builder->build($params['options'], $dict);
				
				$out = sprintf(">>> Prompting with radio buttons\nLabel: %s\nOptions: %s\n",
					$label,
					$options
				);
				break;
				
			case 'prompt_text':
				$tpl_builder = DevblocksPlatform::services()->templateBuilder();
				$label = $tpl_builder->build($params['label'], $dict);
				$placeholder = $tpl_builder->build($params['placeholder'], $dict);
				
				$out = sprintf(">>> Prompting with text input\nLabel: %s\nPlaceholder: %s\n",
					$label,
					$placeholder
				);
				break;
				
			case 'prompt_submit':
				break;
				
			case 'respond_text':
				$tpl_builder = DevblocksPlatform::services()->templateBuilder();
				$content = $tpl_builder->build($params['message'], $dict);
				
				$out = sprintf(">>> Sending response text\n".
					"%s\n",
					$content
				);
				break;
				
			case 'respond_sheet':
				//$tpl_builder = DevblocksPlatform::services()->templateBuilder();
				//$query = $tpl_builder->build($params['data_query'], $dict);
				
				$out = sprintf(">>> Sending sheet as response\n"
				);
				break;
		}
		
		return $out;
	}
	
	function runActionExtension($token, $trigger, $params, DevblocksDictionaryDelegate $dict) {
		switch($token) {
			case 'prompt_captcha':
				$actions =& $dict->_actions;
				
				$tpl_builder = DevblocksPlatform::services()->templateBuilder();
				
				@$var = $params['var'];
				
				$label = 'Please prove you are not a robot:';
				
				// Generate random code
				$otp_key = $var . '__otp';
				$otp = $dict->get($otp_key);
				
				if(!$otp) {
					$otp = CerberusApplication::generatePassword(4);
					$dict->set($otp_key, $otp);
				}
				
				$actions[] = [
					'_action' => 'prompt.captcha',
					'_trigger_id' => $trigger->id,
					'_prompt' => [
						'var' => $var,
					],
					'label' => $label,
				];
				break;
				
			case 'prompt_checkboxes':
				$actions =& $dict->_actions;
				
				$tpl_builder = DevblocksPlatform::services()->templateBuilder();
				
				@$label = $tpl_builder->build($params['label'], $dict);
				@$options = DevblocksPlatform::parseCrlfString($tpl_builder->build($params['options'], $dict));
				@$default = DevblocksPlatform::parseCrlfString($tpl_builder->build($params['default'], $dict));
				@$var = $params['var'];
				@$var_validate = $params['var_validate'];
				
				$actions[] = [
					'_action' => 'prompt.checkboxes',
					'_trigger_id' => $trigger->id,
					'_prompt' => [
						'var' => $var,
						'validate' => $var_validate,
					],
					'label' => $label,
					'options' => $options,
					'default' => $default,
				];
				break;
				
			case 'prompt_chooser':
				$actions =& $dict->_actions;
				
				$tpl_builder = DevblocksPlatform::services()->templateBuilder();
				
				@$label = $tpl_builder->build($params['label'], $dict);
				@$selection = $params['selection'];
				@$autocomplete = !empty($params['autocomplete']);
				@$record_type = $tpl_builder->build($params['record_type'], $dict);
				@$record_query = $tpl_builder->build($params['record_query'], $dict);
				@$record_query_required = $tpl_builder->build($params['record_query_required'], $dict);
				@$var = $params['var'];
				@$var_validate = $params['var_validate'];
				
				$actions[] = [
					'_action' => 'prompt.chooser',
					'_trigger_id' => $trigger->id,
					'_prompt' => [
						'var' => $var,
						'validate' => $var_validate,
					],
					'label' => $label,
					'selection' => $selection,
					'autocomplete' => $autocomplete,
					'record_type' => $record_type,
					'record_query' => $record_query,
					'record_query_required' => $record_query_required,
				];
				break;
				
			case 'prompt_files':
				$actions =& $dict->_actions;
				
				$tpl_builder = DevblocksPlatform::services()->templateBuilder();
				
				@$label = $tpl_builder->build($params['label'], $dict);
				@$selection = $params['selection'];
				@$var = $params['var'];
				@$var_validate = $params['var_validate'];
				
				$actions[] = [
					'_action' => 'prompt.files',
					'_trigger_id' => $trigger->id,
					'_prompt' => [
						'var' => $var,
						'validate' => $var_validate,
					],
					'label' => $label,
					'selection' => $selection,
				];
				break;
				
			case 'prompt_radios':
				$actions =& $dict->_actions;
				
				$tpl_builder = DevblocksPlatform::services()->templateBuilder();
				
				@$label = $tpl_builder->build($params['label'], $dict);
				@$style = $params['style'];
				@$orientation = $params['orientation'];
				@$options = DevblocksPlatform::parseCrlfString($tpl_builder->build($params['options'], $dict));
				@$default = $tpl_builder->build($params['default'], $dict);
				@$var = $params['var'];
				@$var_format = $params['var_format'];
				@$var_validate = $params['var_validate'];
				
				$actions[] = [
					'_action' => 'prompt.radios',
					'_trigger_id' => $trigger->id,
					'_prompt' => [
						'var' => $var,
						'format' => $var_format,
						'validate' => $var_validate,
					],
					'label' => $label,
					'style' => $style,
					'orientation' => $orientation,
					'options' => $options,
					'default' => $default,
				];
				break;
				
			case 'prompt_text':
				$actions =& $dict->_actions;
				
				$tpl_builder = DevblocksPlatform::services()->templateBuilder();
				
				@$label = $tpl_builder->build($params['label'], $dict);
				@$placeholder = $tpl_builder->build($params['placeholder'], $dict);
				@$default = $tpl_builder->build($params['default'], $dict);
				@$mode = $params['mode'];
				@$var = $params['var'];
				@$var_format = $params['var_format'];
				@$var_validate = $params['var_validate'];
				
				$actions[] = [
					'_action' => 'prompt.text',
					'_trigger_id' => $trigger->id,
					'_prompt' => [
						'var' => $var,
						'format' => $var_format,
						'validate' => $var_validate,
					],
					'label' => $label,
					'placeholder' => $placeholder,
					'default' => $default,
					'mode' => $mode,
				];
				break;
			
			case 'prompt_submit':
				$actions =& $dict->_actions;
				
				$actions[] = array(
					'_action' => 'prompt.submit',
					'_trigger_id' => $trigger->id,
				);
				
				$dict->__exit = 'suspend';
				break;
				
			case 'respond_text':
				$actions =& $dict->_actions;
				
				@$format = $params['format'];
				
				$tpl_builder = DevblocksPlatform::services()->templateBuilder();
				$content = $tpl_builder->build($params['message'], $dict);
				
				switch($format) {
					case 'html':
						break;
						
					case 'markdown':
						$content = DevblocksPlatform::parseMarkdown($content);
						break;
					
					default:
						$format = '';
						break;
				}
				
				$actions[] = array(
					'_action' => 'respond.text',
					'_trigger_id' => $trigger->id,
					'message' => $content,
					'format' => $format,
				);
				break;
				
			case 'respond_sheet':
				$actions =& $dict->_actions;
				
				$tpl_builder = DevblocksPlatform::services()->templateBuilder();
				$data_query = $tpl_builder->build($params['data_query'], $dict);
				
				$sheet_yaml = $params['sheet_yaml'];
				
				$actions[] = array(
					'_action' => 'respond.sheet',
					'_trigger_id' => $trigger->id,
					'data_query' => $data_query,
					'sheet_yaml' => $sheet_yaml,
				);
				break;
		}
	}
};