<?php

class ColorTool {
	
	protected $rgbf = array();
		
	public function __construct() {
		$this->rgbf = array(0, 0, 0);
	}
	
	public function hex($hex) {
		if( !$this->is_hex($hex) ) return FALSE;
		$hex = trim($hex, '#');
		if( strlen($hex) == 3 ) {
			$hex = preg_replace('/([0-9a-f])([0-9a-f])([0-9a-f])/', '\1\1\2\2\3\3', $hex);
		}
		list($r, $g, $b) = array(
			hexdec($hex[0].$hex[1])/255,
			hexdec($hex[2].$hex[3])/255,
			hexdec($hex[4].$hex[5])/255
		);
		$this->rgbf = array($r, $g, $b);
		return clone $this;
	}

	public function rgb($r, $g=FALSE, $b=FALSE) {
		if( is_array($r) ) {
			list($r, $g, $b) = $r;
		}
		$r = $r / 255;
		$g = $g / 255;
		$b = $b / 255;
		$this->rgbf = array($r, $g, $b);
		return clone $this;
	}

	public function rgbp($r, $g=FALSE, $b=FALSE) {
		if( is_array($r) ) {
			list($r, $g, $b) = $r;
		}
		$r = $r / 100;
		$g = $g / 100;
		$b = $b / 100;
		$this->rgbf = array($r, $g, $b);
		return clone $this;
	}
	
	public function hsl($h, $s=FALSE, $l=FALSE) {
		if( is_array($h) ) {
			list($h, $s, $l) = $h;
		}
		$h = $h / 360;
		$s = $s / 100;
		$l = $l / 100;
		$this->rgbf = $this->_hsl_to_rgbf( array($h, $s, $l) );
		return clone $this;
	}

	public function is_hex($hex=FALSE)	{
		if(!$hex)
			$hex = $this->_rgbf_to_hex();
		return preg_match('/^#?([0-9a-f]{3}|[0-9a-f]{6})$/i', $hex) == 1;
	}

	public function is_similar($color1, $color2, $delta=50) {
		$delta = $delta / 100;
		$hsl1 = $color1->_rgbf_to_hsl();
		$hsl2 = $color2->_rgbf_to_hsl();
		if( abs( $hsl1['h'] - $hsl2['h'] ) < ( (50*$delta) / 360 )
			&& abs( $hsl1['s'] - $hsl2['s'] ) < ( (60*$delta) /100)
			&& abs( $hsl1['l'] - $hsl2['l'] ) < ( (60*$delta) /100)
		) return TRUE;
		return FALSE;
	}

	public function invert() {
		$r = abs($this->rgbf[0] - 1);
		$g = abs($this->rgbf[1] - 1);
		$b = abs($this->rgbf[2] - 1);
		$this->rgbf = array($r, $g, $b);
		return $this;
	}

	public function saturation($v) {
		if( $v < 0 ) $this->_hsl_decrease('s', abs($v));	
		else $this->_hsl_increase('s', abs($v));
		return $this;
	}
	
	public function saturation_to($v) {
		$this->_hsl_to('s', $v);
		return $this;
	}

	public function light($v) {
		if( $v < 0 ) $this->_hsl_decrease('l', abs($v));	
		else $this->_hsl_increase('l', abs($v));
		return $this;
	}

	public function light_to($v) {
		$this->_hsl_to('l', $v);
		return $this;
	}
	
	public function hue($v) {
		if( $v < 0 ) $this->_hsl_decrease('h', abs($v));	
		else $this->_hsl_increase('h', abs($v));
		return $this;
	}
	
	public function hue_to($v) {
		$this->_hsl_to('h', $v);
		return $this;
	}

	public function to_hex() {
		return $this->_rgbf_to_hex();
	}
	
	public function to_hex_short() {
		$hex = $this->_rgbf_to_hex();
		$shorthand = preg_replace('/([0-9a-f])\1([0-9a-f])\2([0-9a-f])\3/i', '\1\2\3', $hex);
		return $shorthand;
	}
	
	public function to_html() {
		return '#'.$this->to_hex();
	}
	
	public function to_rgb() {
		return array(
			'r' => round($this->rgbf[0]*255),
			'g' => round($this->rgbf[1]*255),
			'b' => round($this->rgbf[2]*255)
		);
	}
	
	public function to_rgbp() {
		return array(
			'r' => round($this->rgbf[0]*100, 2),
			'g' => round($this->rgbf[1]*100, 2),
			'b' => round($this->rgbf[2]*100, 2)
		);
	}

	public function to_rgbf() {
		return array('r' => $this->rgbf[0], 'r' => $this->rgbf[1], 'r' => $this->rgbf[2]);
	}

	public function to_hsl() {
		$hsl = $this->_rgbf_to_hsl();
		return array(
			'h' => round($hsl['h']*360),
			's' => round($hsl['s']*100),
			'l' => round($hsl['l']*100)
		);
	}
	
	protected function _rgbf_to_hex() {
		$r = $this->rgbf[0]*255;
		$g = $this->rgbf[1]*255;
		$b = $this->rgbf[2]*255;
		return sprintf('%02x%02x%02x', $r, $g, $b);
	}
	
	protected function _hsl_decrease($key, $value) {
		$hsl = $this->_rgbf_to_hsl();
		$hsl[$key] = $hsl[$key] - ( $hsl[$key] * ($value/100) );
		if( $hsl[$key] > 1 ) $hsl[$key] = 1;
		if( $hsl[$key] < 0 ) $hsl[$key] = 0;
		$this->rgbf = $this->_hsl_to_rgbf($hsl);
	}
	
	protected function _hsl_increase($key, $value) {
		$hsl = $this->_rgbf_to_hsl();
		$hsl[$key] = $hsl[$key] + ( $hsl[$key] * ($value/100) );
		if( $hsl[$key] > 1 ) $hsl[$key] = 1;
		if( $hsl[$key] < 0 ) $hsl[$key] = 0;
		$this->rgbf = $this->_hsl_to_rgbf($hsl);
	}
	
	protected function _hsl_to($key, $value) {
		$hsl = $this->_rgbf_to_hsl();
		$hsl[$key] = ($value/100);
		if( $hsl[$key] > 1 ) $hsl[$key] = 1;
		if( $hsl[$key] < 0 ) $hsl[$key] = 0;
		$this->rgbf = $this->_hsl_to_rgbf($hsl);
	}

	protected function _rgbf_to_hsl()  {
		list($r, $g, $b) = $this->rgbf;

	    $min = min($r, min($g, $b));
	    $max = max($r, max($g, $b));
	    $delta = $max - $min;
	    $l = ($min + $max) / 2;

	    $s = 0;
	    if($l > 0 && $l < 1)
	      $s = $delta / ($l < 0.5 ? (2 * $l) : (2 - 2 * $l));

	    $h = 0;
	    if($delta > 0)
	    {
	      if ($max == $r && $max != $g) $h += ($g - $b) / $delta;
	      if ($max == $g && $max != $b) $h += (2 + ($b - $r) / $delta);
	      if ($max == $b && $max != $r) $h += (4 + ($r - $g) / $delta);
	      $h = $h/6;
	    }
	    return array(
			'h' => $h,
			's' => $s,
			'l' => $l
		);
	}

	protected function _hsl_to_rgbf($hsl) {
		list($h, $s, $l) = $hsl;
		if( $s == 0) {
			$r = $l;
			$g = $l;
			$b = $l;
		}
		else {
			if( $l < 0.5 )
				$t2 = $l * ( 1 + $s);
			else
				$t2 = ( $l + $s ) - ( $s * $l );
			$t1 = 2 * $l - $t2;
			$r = $this->_hue_to_rgb( $t1, $t2, $h + (1/3) );
			$g = $this->_hue_to_rgb( $t1, $t2, $h );
			$b = $this->_hue_to_rgb( $t1, $t2, $h - (1/3) );
		}
		return array($r, $g, $b);
	}
	
	protected function _hue_to_rgb( $t1, $t2, $h ) {
		if( $h < 0 ) $h++;
		if( $h > 1 ) $h--;
		if( ( 6 * $h ) < 1 ) return ( $t1 + ( $t2 - $t1 ) * 6 * $h );
		if( ( 2 * $h ) < 1 ) return ( $t2 );
		if( ( 3 * $h ) < 2 ) return ( $t1 + ( $t2 - $t1 ) * ( (2/3) - $h ) * 6 );
		return $t1;
	}
	
}

/* End of file color-tools.php */