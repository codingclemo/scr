<?php

class TemplateEngine {

    private static function findTemplateOld(string $tmpl)  {
        $posBegin = strpos($tmpl, "###");
        $ret["found"] = false; 
        \Util::my_var_dump( $posBegin, "TemplateEngine::find_between()   posBegin" );
        if ($posBegin !== false) {
            $tmp = $posBegin + strlen("###") ;
            $posEnd = strpos($tmpl, "###", $tmp);
            $ret["begin"] = $posBegin; 
            $ret["end"] = $posEnd; 
            $ret["found"] = true; 
            $ret["tmplname"] = trim(substr($tmpl, $tmp, $posEnd - $tmp));
        }
        return $ret;
    }

    
    private static function findTemplate(string $tmpl)  {
        $start = "###";
        $end = "###";
        $pattern = '/' . preg_quote($start) . '(.*?';
        $pattern .= ')' . preg_quote($end) . '/s';
        // \Util::my_var_dump( $pattern, "TemplateEngine::find_between()   pattern" );
        $i = preg_match($pattern, $tmpl, $matches);
        $code = null;
        if ($i) {
            $code = $matches[0];
        }$ret = null;
        if ($code !== null ) {
            \Util::my_var_dump( $code, "TemplateEngine::find_between()   code" );
            $ret = array();
            $ret["found"] = true; 
            $ret["tmplname"] = $code;
        } 
        return $ret;
    }

    private static function find_between($string, $start, $end) {
        $pattern = '/' . preg_quote($start) . '(.*?';
        $pattern .= ')' . preg_quote($end) . '/s';
        // \Util::my_var_dump( $pattern, "TemplateEngine::find_between()   pattern" );
        $i = preg_match($pattern, $string, $matches);
        $string = null;
        if ($i) {
            $string = $matches[0];
            $string = substr($string, strlen($start));
            $string = substr($string, 0, -strlen($end));
        }
            
        // \Util::my_var_dump( $i, "TemplateEngine::find_between()  " );
        // \Util::my_var_dump( htmlspecialchars(  $string), "TemplateEngine::find_between()   string" );
        
        return $string;
    }

    private static function find_between_all($string, $start, $end) {
        $pattern = '/' . preg_quote($start) . '(.*?';
        $pattern .= ')' . preg_quote($end) . '/s';
        // \Util::my_var_dump( $pattern, "TemplateEngine::find_between()   pattern" );
        $i = preg_match_all($pattern, $string, $matches);
        // \Util::my_var_dump( htmlspecialchars(  $matches[1][0]), "TemplateEngine::find_between()   matches[1][0]" );
        // \Util::my_var_dump( htmlspecialchars(  $matches[1][1]), "TemplateEngine::find_between()   matches[1][1]" );
        $string = null;
        if ($i) {
            $code = array();
            foreach($matches[1] as $m) {
                // \Util::my_var_dump( htmlspecialchars(  $m), "TemplateEngine::find_between_all()   m = " );
                // $m = substr($m, strlen($start));
                // $m = substr($m, 0, -strlen($end) + 1);
                $code[] = $m;
            }
            $string = implode ( "\n" , $code );
        }
        
        //   $matches[0];
        
        return $string;
    }

    private static function getTemplate(string $tmpl)  {
       
        $res = trim(self::find_between($tmpl, \ApplicationConfig::$BEGIN, \ApplicationConfig::$END));
        // // // $regex = "/$###BEGIN###(.*)$###END###/";
        // $regex = "/(BEGIN)(.+?)(END)/";
        // $matches = preg_split($regex, $tmpl);
        // echo "<br/><br/><br/><br/><br/>";
        // \Util::my_var_dump( htmlspecialchars(  $res), "TemplateEngine::find_between() xxx   res" );
        // extract all comments - which are the real code here
        // 

            // $res = trim(self::find_between_all($res, "<!-- ", " -->"));
    
        
        // echo "<br/><br/><br/><br/><br/>";
        // \Util::my_var_dump( htmlspecialchars($res), "TemplateEngine::getTemplate()   res with only  comments " );
        // echo "<br> hallo";
        return $res;
    }

    // private static function findTemplateByName(string $tmpl, string $tmplname) : int  {
    //     $searchString = "<!-- ###" . $tmplname . "###";
    //     return  strpos($tmpl, $searchString);
    // }

    private static function findTemplateByName(string $tmpl, string $tmplname) : int  {
        return strpos($tmpl, $tmplname);
    }

    private static function replaceVariable(string $html, $data, $variable) : string   {
        if (!isset($data[$variable])) {
            // // \Util::my_var_dump( $data, "TemplateEngine::replaceVariable()   could not find key " . $variable . " in object data  ");
            \Logger::logError("TemplateEngine::replaceVariable()   could not find key " . $variable . " in object data " , "");
            // readfile('static/500.html');
            exit();
        }
        // echo "<br> in replaceVariable!!! yeah baby! <br/>";
        // // \Util::my_var_dump( $data, "replaceVariable()   data = ");
        // // \Util::my_var_dump( htmlspecialchars($html) , "replaceVariable()   html = ");
        // // \Util::my_var_dump( $variable , "replaceVariable()   variable = ");

        $html = str_replace("###" . $variable . "###", $data[$variable], $html);
        // // \Util::my_var_dump( htmlspecialchars($html) , "replaceVariable() new with replaced text  html = ");

        return $html;
    }
    

    private static function renderIf(string $template, $data): string {
        $template = trim(self::find_between_all($template, "<!-- ", " -->"));
        \Util::my_var_dump(htmlspecialchars($template), "renderIf()  template only comment = ");

        $template = trim(self::find_between_all($template, \ApplicationConfig::$TEMPLATEIF, \ApplicationConfig::$TEMPLATEIFEND));
        \Util::my_var_dump(htmlspecialchars($template), "renderIf()  template only HTML Code with ###ELSE### = ");
        

        $variable = self::readVariable($template);
        // remove variable string
        $tmp = strlen("###" . $variable . "###"); 
        $template = trim(substr($template, $tmp+1, strlen($template)- $tmp));
        \Util::my_var_dump(htmlspecialchars($variable), "renderIf()  IF clause variable = ");
        \Util::my_var_dump(htmlspecialchars($template), "renderIf()  template only HTML Code with ###ELSE### = ");

        $objKey = strtolower($variable);
        if (!isset($data[$objKey])) {
            \Util::my_var_dump( $data, "TemplateEngine::replaceVariable()   could not find key " . $objKey . " in object data  ");
            \Logger::logError("TemplateEngine::renderIf()   could not find key " . $objKey . " in object data " , "");
            // readfile('static/500.html');
            exit();
        }

        $template = trim($template);
        echo "<br> <br> IF  ...  html code = " . htmlspecialchars($template) ."<br><br>";
        // // \Util::my_var_dump( $variable, "renderForEach()   variable = ");
        // // \Util::my_var_dump( $data, "renderForEach()   data = ");
        // // \Util::my_var_dump( htmlspecialchars($template), "renderForEach()  template = ");
        $html = '';

        $elseTag = \ApplicationConfig::$TEMPLATEIFELSE;
        $posElse = strpos($template,  $elseTag);
        if ($posElse == 0) {
            \Util::my_var_dump( $data, "TemplateEngine::renderIf()   could not find IF for variable  '" . $variable . "' in html template  ");
            \Logger::logError("TemplateEngine::renderIf()   could not find IF for variable  '" . $variable . "' in html template" , "");
            // readfile('static/500.html');
            exit();
        }
        
        $trueHtml = trim(substr($template, 0, $posElse));
        $falseHtml = trim(substr($template, $posElse +strlen($elseTag), strlen($template) - $posElse));

        \Util::my_var_dump( htmlspecialchars($trueHtml), "TemplateEngine::renderIf()   trueHtml  ");
        \Util::my_var_dump( htmlspecialchars($falseHtml), "TemplateEngine::renderIf()   falseHtml  ");

        if ($data[$objKey]) {
            $html = self::renderPartialString($trueHtml, $data) . "\n";
            // // \Util::my_var_dump( htmlspecialchars($html) , "for loop new html  = ");
        } else {
            $html = self::renderPartialString($falseHtml, $data) . "\n";
        }
        \Util::my_var_dump( htmlspecialchars($html), "TemplateEngine::renderIf()   newHtml =  ");

        return $html;
    }

    private static function renderForEach(string $template, $data, $variable): string {
       
        // echo "<br> <br> for loop html code = " . htmlspecialchars($template) ."<br><br>";
        $objKey = strtolower($variable);
        // // \Util::my_var_dump( $variable, "renderForEach()   variable = ");
        // // \Util::my_var_dump( $data, "renderForEach()   data = ");
        // // \Util::my_var_dump( htmlspecialchars($template), "renderForEach()  template = ");
        $html = '';

        if (!isset($data[$objKey])) {
            // \Util::my_var_dump( $data, "TemplateEngine::renderForEach()   could not find key " . $objKey . " in object data   = ");
            \Logger::logError("TemplateEngine::renderForEach()   could not find key " . $objKey . " in object data " , "");
            // readfile('static/500.html');
            exit();
        }

        // remove the plural s from the variable
        $var = trim(substr($variable, 0, strlen($variable)-1));
        foreach ($data[$objKey] as $val ) {
            // // \Util::my_var_dump( $val, "for loop  val  = ");
            // // \Util::my_var_dump( htmlspecialchars($template), "template  = ");
            $html = $html . self::renderPartialString($template, $val) . "\n";
            // // \Util::my_var_dump( htmlspecialchars($html) , "for loop new html  = ");
        }
        return $html;
    }

    private static function renderPartial(string $tmplFilename, $data) : string {
        // echo "<br> 'renderPartial with tmplFilename = " . $tmplFilename ."<br/>"; 
        $partial = file_get_contents ($tmplFilename);
        if (!$partial) {
            \Logger::logError("TemplateEngine::render()   could not open file : " , $tmplFilename);
            readfile('static/500.html');
            exit();
        }
        return self::renderPartialString($partial, $data);
    } 
    private static function readVariable($html) {
        $var = self::findTemplate($html);
        if (!$var["found"]) {
            \Util::my_var_dump( htmlspecialchars($html), "Fatal Error - no variable found in template    ");
            \Logger::logError("Fatal Error - no variable found in template" ,  "");
            readfile('static/500.html');
            exit();
        }
        return str_replace("###", "", $var["tmplname"]);
    }

    private static function renderPartialString(string $partial, $data) : string {
        $templateBegin = self::findTemplateByName($partial,\ApplicationConfig::$TEMPLATEBEGIN);
        if ($templateBegin !== 0) {
            $templateEnd = self::findTemplateByName($partial,\ApplicationConfig::$TEMPLATEEND);
            if ($templateEnd === 0) {
                \Logger::logError("Fatal Error - closing " . \ApplicationConfig::$TEMPLATEEND . " not found in file " ,  $tmplFilename);
                readfile('static/500.html');
                exit();
            }
            // echo "<br>  found a TEMPLATEBEGIN keyowrd at pos  " . $templateBegin . "<br>";
            // echo "<br>  found a TEMPLATEENDkeyowrd at pos  " . $templateEnd . "<br>";

            // well - there it is: subtract 2 and it works
            $partial =  substr($partial, $templateBegin + strlen(\ApplicationConfig::$TEMPLATEBEGIN), $templateEnd - $templateBegin  - strlen(\ApplicationConfig::$TEMPLATEEND)-2);
        } 
        // // \Util::my_var_dump( htmlspecialchars($partial), "renderPartialString()    partial  = ");


        $template = self::getTemplate($partial);
        echo "<br/><br/><br/>";
        \Util::my_var_dump( htmlspecialchars($template), "renderPartialString()    template  = ");
        echo "<br/><br/><br/>";

        if ($template !== null) {
            $nextTemplate = self::findTemplate($template);

            \Util::my_var_dump( $nextTemplate, "nextTemplate  = ");
        
            while ($nextTemplate["found"]) {
                switch ($nextTemplate["tmplname"]) {
                    case \ApplicationConfig::$TEMPLATEFOREACHBEGIN:
                        echo "<br><br>xxxxxxxxxxxxxxxxxxxxxxx<br>found for each<br>xxxxxxxxxxxxxxxxxxxxxxx<br><br><br>";

            //             $foreachEnd = self::findTemplateByName($partial, \ApplicationConfig::$TEMPLATEFOREACHEND);
            //             if ($foreachEnd === FALSE) {
            //                 \Logger::logError("Fatal Error - closing " . \ApplicationConfig::$TEMPLATEFOREACHEND . " not found in file " ,  $tmplFilename);
            //                 readfile('static/500.html');
            //                 exit();
            //             }

            //             // BIG TODO: remove this BS and use regexes


            //             $tmp = trim(substr($partial, $nextTemplate["begin"], $nextTemplate["end"] - $nextTemplate["begin"]));
            //             // // \Util::my_var_dump(htmlspecialchars( $tmp), "tmp  = ");

            //             // read variable name 
            //             $tmp = "<!-- ###" . \ApplicationConfig::$TEMPLATEFOREACHBEGIN . "### ";
            //             $idxEndForEach = strpos($partial, $tmp ) +strlen($tmp);
            //             $endComment = strpos($partial, "-->", $idxEndForEach);
            //             // // \Util::my_var_dump(htmlspecialchars( $tmp), "tmp  = ");

            //             // // \Util::my_var_dump(strlen($idxEndForEach), "idxEndForEach  = ");
            //             // // \Util::my_var_dump(strlen($endComment), "endComment  = ");

            //             $variable = trim(substr($partial, $idxEndForEach, $endComment - $idxEndForEach));
            //             // // \Util::my_var_dump(strlen($variable), "strlen(variable)  = ");

            //             // \Util::my_var_dump(htmlspecialchars( $variable), "variable  = ");

            //             $tmp = $tmp . $variable . " -->";

            //             // // \Util::my_var_dump(htmlspecialchars( $tmp), "full starting tag tmp  = ");
            //             $posBeginFor = $nextTemplate["begin"] + strlen($tmp) ;


            //             $endTagForEach = "<!-- ###" . \ApplicationConfig::$TEMPLATEFOREACHEND . "### " . $variable ." -->";
            //             // // \Util::my_var_dump(htmlspecialchars( $endTagForEach), "full endTagForEach  = ");
            //             $idxEnd = strpos($partial, $endTagForEach);
            //             $forloopTmp = trim(substr($partial, $posBeginFor , $idxEnd - $posBeginFor));
            //             // // \Util::my_var_dump(htmlspecialchars($forloopTmp), "html code forloopTmp  = ");
                        
            //             // forLoopTmp contains the foor loop in <!-- --> and the html code -> cut out the template code
            //             $beginTag = "<!--T";
            //             $endTag = "T-->";
            //             $startIndex = strpos($forloopTmp, $beginTag) + strlen($beginTag);
            //             $endIndex = strpos($forloopTmp, $endTag) ;

            //             $forLoopHtml = trim(substr($forloopTmp, $startIndex, $endIndex - $startIndex));
                        
            //             // \Util::my_var_dump(htmlspecialchars($forLoopHtml), "renderPartialString()   forLoopHtml  = ");
            //             // // \Util::my_var_dump($data, "renderPartialString ()  data  = ");
            //             // \Util::my_var_dump($variable, "renderPartialString ()  variable  = ");

            //             $htmlForLoop = self::renderForEach($forLoopHtml, $data, $variable);
            //             // renderForEach(string $template, $data, $variable)

            //             // // TODO: this is not the best way? replace the $$forloop with some ???

            //             $startTag  = "<!-- ###" . \ApplicationConfig::$TEMPLATEFOREACHBEGIN . "### " . $variable ." -->";
            //             $endTag  = "<!-- ###" . \ApplicationConfig::$TEMPLATEFOREACHEND . "### " . $variable ." -->";

            //             $startIdx = strpos($partial, $startTag);
            //             $endIdx = strpos($partial, $endTag, $startIdx) + strlen($endTag);

            //             // \Util::my_var_dump(htmlspecialchars($startTag), "renderPartialString()  startTag  = ");
            //             // \Util::my_var_dump(htmlspecialchars($endTag), "renderPartialString()  endTag  = ");
            //             // \Util::my_var_dump($startIdx, "renderPartialString()  startIdx  = ");
            //             // // \Util::my_var_dump($endIdx, "renderPartialString()  endIdx  = ");


            //             $strToBeReplaced = substr($partial, $startIdx, $endIdx - $startIdx);
            //             // \Util::my_var_dump(htmlspecialchars($htmlForLoop), "renderPartialString()  this is the resulting html code  = ");
            //             // // \Util::my_var_dump(htmlspecialchars($strToBeReplaced), "renderPartialString()  this is the code we want to replace  strToBeReplaced   = ");


            //             $partial = str_replace($strToBeReplaced, $htmlForLoop, $partial);
                        break;

                    case \ApplicationConfig::$TEMPLATEIF:
                        echo "<br><br>xxxxxxxxxxxxxxxxxxxxxxx<br>found IF<br>xxxxxxxxxxxxxxxxxxxxxxx<br><br><br>";
                        
                        $strToBeReplaced = $template;
                        \Util::my_var_dump(htmlspecialchars($strToBeReplaced), "renderPartialString()  strToBeReplaced  = ");

                        
                        $newHtmlIf = self::renderIf($template, $data);
                        die();
                        
                        $strToBeReplaced = $htmlIf; 

                        \Util::my_var_dump(htmlspecialchars($template), "renderPartialString()  template  = ");
                        
                        \Util::my_var_dump(htmlspecialchars($htmlIf), "renderPartialString()  IF clause  = ");

                       
                        // // remove variable string
                        // $tmp = strlen("###" . $variable . "###"); 
                        // $htmlIf = trim(substr($htmlIf, $tmp+1, strlen($htmlIf)- $tmp));
                        // \Util::my_var_dump(htmlspecialchars($htmlIf), "renderPartialString()  htmlIf ohne variable  = ");

                        // $htmlIf = self::renderIf($htmlIf, $data, $variable);
                        // \Util::my_var_dump(htmlspecialchars($htmlIf), "renderPartialString() IF  this is the new code    htmlIf   = ");


                        // \Util::my_var_dump(htmlspecialchars($strToBeReplaced), "renderPartialString() IF  this strToBeReplaced  = ");

                        // \Util::my_var_dump(htmlspecialchars($template), "renderPartialString() IF  this is the OLD template code  = ");
                        // $template = str_replace($strToBeReplaced, $htmlIf, $template);
                        // \Util::my_var_dump(htmlspecialchars($template), "renderPartialString() IF  this is theNEW  template code  = ");
                        die();
                        break;
                    
                    default:
                        echo "<br><br>xxxxxxxxxxxxxxxxxxxxxxx<br>found a variable which should be subsituted<br>xxxxxxxxxxxxxxxxxxxxxxx<br><br><br>";
            //             $partial = self::replaceVariable($partial, $data, $nextTemplate["tmplname"]);
                        break;
                }
                $nextTemplate = self::findTemplate($partial);
            }   
        }
        // // \Util::my_var_dump($nextTemplate, "next template = ");
        // echo "<br><br> html = " . htmlspecialchars($html) . "<br><br>";

        return $partial;
    } 


    // template names can be null -> no string type  
    public static function render(string $tmplFilename, $data,  $headertemplate,  $contenttemplate,  $footertemplate) : string {
        $template = file_get_contents ( $tmplFilename);
        if (!$template) {
            \Logger::logError("TemplateEngine::render()   could not open file : " , $tmplFilename);
            readfile('static/500.html');
            exit();
        }

        // check if there is a header, footer or content template
        // if ($headertemplate !== null) {
        //     $headerBegin = self::findTemplateByName($template, \ApplicationConfig::$TEMPLATEHEADER);
        //     if  ($headerBegin !== FALSE) {
        //         // echo "<br> found a '###TEMPLATE_HEADER###'";
        //         // echo "<br><br> template BEFORE replacing = " . htmlspecialchars($template) . "<br><br>";
        //         $template = str_replace(\ApplicationConfig::$TEMPLATEHEADER, self::renderPartial($headertemplate, $data), $template);
        //         // echo "<br><br> template AFTER replacing = " . htmlspecialchars($template) . "<br><br>";
        //     }
        // }
        
        if ($contenttemplate !== null) {
            $contentBegin = self::findTemplateByName($template, \ApplicationConfig::$TEMPLATECONTENT);
            if  ($contentBegin !== FALSE) {
                // echo "<br> found a '###TEMPLATE_CONTENT###'";
                $template = str_replace(\ApplicationConfig::$TEMPLATECONTENT, self::renderPartial($contenttemplate, $data), $template);
            }
        }

        // if ($footertemplate !== null) {
        //     $footerBegin = self::findTemplateByName($template, \ApplicationConfig::$TEMPLATEFOOTER);
        //     if  ($footerBegin !== FALSE) {
        //         // echo "<br> found a '###TEMPLATE_FOOTER###'";
        //         $template = str_replace(\ApplicationConfig::$TEMPLATEFOOTER, self::renderPartial($footertemplate, $data), $template);

        //     }
        // }
        // file_put_contents('tmp.html', $template);
        return $template;
    }
}
