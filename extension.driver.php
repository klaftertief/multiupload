<?php

	Class extension_Multiupload extends Extension {

	/*-------------------------------------------------------------------------
		Definition:
	-------------------------------------------------------------------------*/

		public function getSubscribedDelegates() {
			return array(
				array(
					'page'		=> '/blueprints/events/new/',
					'delegate'	=> 'AppendEventFilter',
					'callback'	=> 'appendEventFilter'
				),
				array(
					'page'		=> '/blueprints/events/edit/',
					'delegate'	=> 'AppendEventFilter',
					'callback'	=> 'appendEventFilter'
				),
				array(
					'page'		=> '/frontend/',
					'delegate'	=> 'EventPreSaveFilter',
					'callback'	=> 'eventPreSaveFilter'
				),
			);
		}

	/*-------------------------------------------------------------------------
		Delegates:
	-------------------------------------------------------------------------*/

		public function appendEventFilter($context) {
			$context['options'][] = array(
				'multiupload',
				@in_array(
					'multiupload', $context['selected']
				),
				__('Multiupload')
			);
		}

		public function eventPreSaveFilter($context) {
			if (in_array('multiupload', $context['event']->eParamFILTERS)) {

				$fields = $context['fields'];
				$multiupload = $_POST['multiupload'];

				if (is_array($multiupload) && !empty($multiupload) && $multiupload['trigger'] === 'yes') {
					// Create upload directory
					// TODO safety!!!!
					// General::realiseDirectory(WORKSPACE . $multiupload['upload-dir'], Symphony::Configuration()->get('write_mode', 'directory'));

					require('lib/upload.class.php');

					// TODO defaults and disallow upload to random directories
					$upload_handler = new UploadHandler(array(
						'script_url' => URL . $multiupload['script-url'], // '/administration/media/images/new'
						'upload_dir' => WORKSPACE . $multiupload['upload-dir'] . '/', // '/media/images/',
						'upload_url' => URL . $multiupload['upload-url'], // '/workspace/media/images/',
						'param_name' => 'fields',
					));

					header('Pragma: no-cache');
					header('Cache-Control: no-store, no-cache, must-revalidate');
					header('Content-Disposition: inline; filename="files.json"');
					header('X-Content-Type-Options: nosniff');
					header('Access-Control-Allow-Origin: *');
					header('Access-Control-Allow-Methods: OPTIONS, HEAD, GET, POST, PUT, DELETE');
					header('Access-Control-Allow-Headers: X-File-Name, X-File-Type, X-File-Size');

					switch ($_SERVER['REQUEST_METHOD']) {
						case 'OPTIONS':
							break;
						case 'HEAD':
						case 'GET':
							$upload_handler->get();
							break;
						case 'POST':
							if (isset($_REQUEST['_method']) && $_REQUEST['_method'] === 'DELETE') {
								$upload_handler->delete();
							} else {
								$upload_handler->post();
							}
							break;
						case 'DELETE':
							$upload_handler->delete();
							break;
						default:
							header('HTTP/1.1 405 Method Not Allowed');
					}

					die;
				}
			}
		}

	}