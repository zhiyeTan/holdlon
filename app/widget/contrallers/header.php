<?php

use z\core\Widget as widget;

if(!widget::chkAssign('hdata'))
{
	widget::assign('hdata', 'this is a header data!!!!!!');
}

