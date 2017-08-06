<?php

use z\core\widget as widget;

if(!widget::chkAssign('hdata'))
{
	widget::assign('hdata', 'this is a header data!!!!!!');
}

