<?php

return array(
	'time'		=> microtime(true) - Z_BEGIN_TIME,
	'momery'	=> (memory_get_usage() - Z_BEGIN_MEM)/1024
);
