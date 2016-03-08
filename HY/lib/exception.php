<?php


namespace HY;
// 仅仅用来显示 Exception
class exception {
	/*
	// 异常CODE,用来标示是哪个模块产生的异常。暂时不需要。
	const APP_EXCEPTION_CODE_ERROR_HANDLE = 1;
	const APP_EXCEPTION_CODE_MYSQL = 2;
	const APP_EXCEPTION_CODE_LOG = 3;
	*/
	public static function format_exception($e) {
		$trace = $e->getTrace();

		// 如果是 error_handle ，弹出第一个元素。
		if(!empty($trace) && $trace[0]['function'] == 'error_handle' && $trace[0]['class'] == 'core') {
			//array_shift($trace);
			$line = $trace[0]['args'][3];
			$file = $trace[0]['args'][2];
			$message = $trace[0]['args'][1];


		} else {
			$line = $e->getLine();
			$file = $e->getFile();
			$message = $e->getMessage();
		}
		if(!empty(DEBUG_SQL::$logs)){
			$_SERVER['sqls'] = DEBUG_SQL::$logs;

			$_SERVER['new_class'] = HY::$_CLASS;

		}


		$backtracelist = array();
		foreach($trace as $k=>$v) {
			$args = $comma = '';
			if(!empty($v['args'])) {
				if(DEBUG) {
					if($v['function'] == 'error_handle') {
						$v['class'] = '';
						$v['function'] = '';
						$args = '';
					} else {
						foreach((array)$v['args'] as $arg) {

							//var_dump($arg);
							if(is_string($arg)) {
								$args .= $comma."'$arg'";
							} elseif(is_object($arg)) {
								$args .= $comma."Object";
							} else {
								$args .= $comma.''.($arg === NULL ? 'NULL' : $arg);
							}
							$comma = ', ';
						}
					}
				} else {
					$args = '';
				}
			}
			!isset($v['file']) && $v['file'] = '';
			!isset($v['line']) && $v['line'] = '';
			!isset($v['function']) && $v['function'] = '';
			!isset($v['class']) && $v['class'] = '';
			!isset($v['type']) && $v['type'] = '';

			$backtracelist[] = array (
				'file'=>$v['file'],
				'line'=>$v['line'],
				'function'=>$v['function'],
				'class'=>$v['class'],
				'type'=>$v['type'],
				'args'=>$args,
			);
		}
		// array_shift($backtracelist);
		// array_shift($backtracelist);

		$codelist = self::get_code($file, $line);

		return array(
			'line'=>$line,
			'file'=>$file,
			'codelist'=>$codelist,
			'message'=>$message,
			'backtracelist'=>$backtracelist,
		);
	}

	public static function get_code($file, $line) {
		$arr = file($file);
		$arr2 = array_slice($arr, max(0, $line - 5), 10, true);

		foreach ($arr2 as &$v) {
			$v = htmlspecialchars($v);
			$v = str_replace(' ', '&nbsp;', $v);
			$v = str_replace('	', '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', $v);
		}

		return $arr2;
	}

	public static function to_text($e) {
		return self::to_string();
	}

	public static function to_html($e) {
		// $s 可能是一个字符串，也可能是一个对象
		return self::to_string($e);
	}

	public static function to_string($e) {
		if(is_object($e)) {
			$arr = self::format_exception($e);
			$line = $arr['line'];
			$file = $arr['file'];
			$codelist = $arr['codelist'];
			$message = $arr['message'];
			$backtracelist = $arr['backtracelist'];

			ob_start();

			include HY_PATH . 'View/exception.htm';

			$s = ob_get_contents();
			ob_end_clean();
			return $s;
		} elseif(is_string($e)) {
			return $e;
		}
	}

}

?>
