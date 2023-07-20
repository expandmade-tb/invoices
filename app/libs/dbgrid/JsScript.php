<?php

namespace dbgrid;

class JsScript {
    private static ?JsScript $instance = null;
    private array $inline_scripts = [];
    private array $inline_vars = [];
    private int $new_var = 0;

    protected function __construct() {
    }

    public static function instance() : JsScript {
        if ( self::$instance == null )
            self::$instance = new JsScript();
   
        return self::$instance;
    }

    public function add_script(string $script, string $param='') : JsScript {
        $this->inline_scripts[$script] = $param;
        return $this;
    }

    public function var(string $var, string $value) : JsScript {
        $this->inline_vars[$var] = $value;
        return $this;
    }

    public function add_var(string $value) : string {
        $this->new_var += 1;
        $var = "v$this->new_var";
        $this->inline_vars["$var"] = $value;
        return $var;
    }

    public function generate () : string {
        $vars = '';
        $scripts = '';
        $js = JAVASCRIPT;

        foreach ($this->inline_vars as $key => $value)
            $vars .= "var $key='$value';";

        foreach ($this->inline_scripts as $key => $value)
            $scripts .= "<script src=\"$js/$key.min.js\"></script>";

        return "<script>$vars</script>".$scripts;
    }
}