<?php

namespace Menu;

class MenuBar { 
   private static ?MenuBar $instance = null;
 
   public static function factory () : MenuBar {
      if (self::$instance == null)
         self::$instance = new MenuBar();

      return self::$instance;
   }

   private function build_menu (array $menu, int $level=0) : string {  /* recursive function ! */
      $result = '';

      foreach ($menu as $item => $link) {
         if ( is_array($link) ) {
            if ( $level > 0 )
               $result .= '<li class="nav-item dropend"> <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">'.$item.'</a> <ul class="dropdown-menu dropdown-menu-dark">';
            else
               $result .= '<li class="nav-item dropdown"> <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">'.$item.'</a> <ul class="dropdown-menu dropdown-menu-dark">';
            
            $i = $level + 1;
            $result .= $this->build_menu($link, $i).'</ul></li>';
         }
         else {
            if ( $level > 0 )
               $result .= '<li> <a class="dropdown-item" href="'.$link.'">'.$item.'</a> </li>';
            else
               $result .= '<li class="nav-item"> <a class="nav-link" href="'.$link.'">'.$item.'</a> </li>';
         }
      }

      return $result;
   }

   public function get(string $menu='menu') : string {
      $menu = require(__DIR__ . "/$menu.php");
      return $this->build_menu($menu);
   }
}