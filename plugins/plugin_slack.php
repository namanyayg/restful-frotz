<?
	#
	# plugin_slack
	# A slack plugin for restful-frotz
	#

	#
	# Slack specific input handler. Will convert the slack 
	# outgoing webhook data to what's required for restful-frotz
	#
	function handler_input(&$params){

		$command = str_replace($params['trigger_word'], "", $params['text']);
		if (substr($command, 0, 1) != " "){
			#
			# The trigger was not followed by a space,
			# so they might have just started a sentance with
			# the trigger word, so bail with no output.
			#
			die;
		}

		$params['command'] = substr($command, 1);
	}


	#
	# Slack specific output handler. 
	# Will convert the restful-frotz data to what's required for a 
	# Slack incomign webhook, and call the hook.
	#
	function handler_output($data){

		if (!$_REQUEST['output-webhook']){
			return array('ok' => 0, 'error' => 'config error - missing slack incoming webhook');
		}

		if ($data['error']){
			$attachment = array(
				'text' 	   => $data['error'],
				'fallback' => $data['error'],
				'color'    => 'dddddd',
			);

		}else{
			$attachment = array(
				'title'    => trim($data['title']),
				'text'     => $data['message'],
				'fallback' => $data['title'],
				'color'    => '333342',
			);
		}

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $_REQUEST['output-webhook']);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, array('payload' => json_encode(array('attachments' => array($attachment)))));
		curl_setopt($ch, CURLOPT_FAILONERROR, TRUE);

		$ret = curl_exec($ch);
		if (!$ret) $curl_error = curl_error($ch);

		curl_close($ch);

		if ($curl_error){
			return array('ok' => 0, 'error' => $curl_error);
		}

		return array('ok' => 1);
	}


	#
	# Slack specific error handler.
	# Will output the error to the Slack outgoing webhook response.
	#
	function handler_error($error){

		error_log("restful-frotz [slack] error: ".$error);
		die(json_encode(array('ok' =>0, 'text' => $error)));
	}

