<?php

namespace Glass;

class TML {
  private static $fontChain     = [];
  private static $fontSizeChain = [];
  public static function format($text, $font = "verdana", $size = 13) {
    $fontBold = $font . " bold";
    $fontItalic = $font . " italic";

    $len = strlen($text);
    $idx = 0;

    $escaped = false;

    $lastChar = "";
    $char = "";

    $inBold = false;
    $inItalic = false;

    $tml = TML::changeFont($font, $size);

    $emphasisCt = 0;

    while($idx < $len) {
      $lastChar = $char;
      $char = substr($text, $idx, 1);
      $idx++;

      if($escaped) {
        $tml .= $char;
        $char = "\\" . $char;
        $escaped = false;
        continue;
      }

      if($char == "\\") {
        $escaped = true;
        continue;
      }

      if($char == "_") {
        $emphasisCt++;
        continue;
      }

      if($lastChar == "_") {
        if($char != "_") {
          if($emphasisCt == 2) {
            if($inBold) {
              $tml .= TML::revertFont();
            } else {
              $tml .= TML::changeFont($fontBold);
            }
            $inBold = !$inBold;
          } else if($emphasisCt == 1) {
            if($inItalic) {
              $tml .= TML::revertFont();
            } else {
              $tml .= TML::changeFont($fontItalic);
            }
            $inItalic = !$inItalic;
          }
          $emphasisCt = 0;
          $tml .= $char;
          continue;
        }
      }

      switch($char) {
        case "\n":
          $tml .= "<br>";
          if($lastChar == "\n") {
            $tml .= "<lmargin:0>";
          }
          break;

        case "-":
        case "+":
        case "*":
          if(trim($lastChar) == "") {
            $tml .= "<lmargin:10>";
            $tml .= "<bitmap:Add-Ons/System_BlocklandGlass/image/icon/bullet_black>";
            $tml .= "<lmargin:26>";
          } else {
            $tml .= $char;
          }
          break;

        default:
          $tml .= $char;
      }
    }
    return $tml;
  }

  private static function changeFont($font, $size = false) {
    if($size === false) {
      $size = TML::fontSize();
    }
    array_push(TML::$fontChain, $font);
    array_push(TML::$fontSizeChain, $size);
    return "<font:" . $font . ":" . $size . ">";
  }

  private static function revertFont() {
    array_pop(TML::$fontChain);
    array_pop(TML::$fontSizeChain);

    $fontChain = TML::$fontChain;
    $fontSizeChain = TML::$fontSizeChain;

    $font = $fontChain[sizeof($fontChain)-1];
    $size = $fontSizeChain[sizeof($fontSizeChain)-1];

    return "<font:" . $font . ":" . $size . ">";
  }

  private static function fontSize() {
    $fontSizeChain = TML::$fontSizeChain;
    $size = $fontSizeChain[sizeof($fontSizeChain)-1];
    return $size;
  }
}
