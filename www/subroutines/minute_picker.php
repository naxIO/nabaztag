<?php
/*
NabaztagLives 

Copyright (C) 2013 Pokey (Pokey@nabaztaglives.com)

Comments, questions, and bug reports should be submitted via
http://sourceforge.net/projects/nabaztaglives/

More details can be found at the project home page:
http://nabaztaglives.com

This file is part of NabaztagLives.

NabaztagLives is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

NabaztagLives is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with NabaztagLives.  If not, see <http://www.gnu.org/licenses/>.
*/

if(! isset($s_min)) $s_min = 'none';
?>

<option <?php if($s_min == '00') echo 'selected'; ?>>00</option>
<option <?php if($s_min == '05') echo 'selected'; ?>>05</option>
<option <?php if($s_min == '15') echo 'selected'; ?>>15</option>
<option <?php if($s_min == '25') echo 'selected'; ?>>25</option>
<option <?php if($s_min == '30') echo 'selected'; ?>>30</option>
<option <?php if($s_min == '35') echo 'selected'; ?>>35</option>
<option <?php if($s_min == '45') echo 'selected'; ?>>45</option>
<option <?php if($s_min == '55') echo 'selected'; ?>>55</option>
