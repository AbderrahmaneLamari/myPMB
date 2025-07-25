<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: tags.php,v 1.15 2021/12/24 14:33:07 qvarin Exp $

/**
 *
 * @author taylor.luk
 * @todo tags need more test coverage
 */

/**
 * ifchanged tag
 *
 * Usage:
 *
 * Variable mode
 * {% ifchanged data.date %}...{% endifchanged %}
 *
 * Lazy mode *not implemented in h2o yet
 * {% ifchanged %}...{{ data.date }}...{% endifchanged %}
 *
 */
class IfChanged_Tag extends H2o_Node {
    private $nodelist_true;
    private $nodelist_false;
    private $_varlist = null;
    private $_last_seen = null;

    public function __construct($argstring, $parser, $position = 0) {
        $this->nodelist_true = $parser->parse('endifchanged', 'else');

        if ($parser->token->content === 'else')
            $this->nodelist_false = $parser->parse('endifchanged');

        $this->_varlist = current(H2o_Parser::parseArguments($argstring));

        if (!$this->_varlist)
            throw new TemplateSyntaxError('H2o doesn\'t support lazy ifchanged yet. Please, supply a variable.');

    }

    public function render($context, $stream) {

        if ($this->_varlist) {
            $compare_to = $context->resolve($this->_varlist);
        } else {
            /**
             * @todo Rendering method $this->nodelist_true->render() should return a result.
             * Further more $compare_to variable should be set to this result.
             */
            $compare_to = '';
        }

        if ($compare_to != $this->_last_seen) {
            $this->_last_seen = $compare_to;
            $this->nodelist_true->render($context, $stream);
        } elseif ($this->nodelist_false) {
            $this->nodelist_false->render($context, $stream);
        }

    }
}

class If_Tag extends H2o_Node {
    private $body;
    private $else;
    private $negate;

    public function __construct($argstring, $parser, $position = 0) {
        $this->body = $parser->parse('endif', 'else');

        if ($parser->token->content === 'else')
            $this->else = $parser->parse('endif');

        $this->args = H2o_Parser::parseArguments($argstring);

        $first = current($this->args);
        if (isset($first['operator']) && $first['operator'] === 'not') {
            array_shift($this->args);
            $this->negate = true;
        }
    }

    public function render($context, $stream) {
        if ($this->test($context))
            $this->body->render($context, $stream);
        elseif ($this->else)
            $this->else->render($context, $stream);
    }

    public function test($context) {
        $test = Evaluator::exec($this->args, $context);
        return $this->negate? !$test : $test;
    }
}

class For_Tag extends H2o_Node {
    public $position;
    private $iteratable, $key, $item, $body, $else, $limit, $reversed;
    private $syntax = '{
        ([a-zA-Z][a-zA-Z0-9-_]*)(?:,\s?([a-zA-Z][a-zA-Z0-9-_]*))?
        \s+in\s+
        ([a-zA-Z][a-zA-Z0-9-_]*(?:\.[a-zA-Z_0-9][a-zA-Z0-9_-]*)*)\s*   # Iteratable name
        (?:limit\s*:\s*(\d+))?\s*
        (reversed)?                                                     # Reverse keyword
    }x';

    public function __construct($argstring, $parser, $position) {
        $matches = [];
        if (!preg_match($this->syntax, $argstring, $matches))
            throw new TemplateSyntaxError("Invalid for loop syntax ");

        $this->body = $parser->parse('endfor', 'else');

        if ($parser->token->content === 'else')
            $this->else = $parser->parse('endfor');

        $match = array_pad($matches, 6, '');
        list(,$this->key, $this->item, $this->iteratable, $this->limit, $this->reversed) = $match;

        if ($this->limit)
            $this->limit = (int) $this->limit;

        # Swap value if no key found
        if (!$this->item) {
            list($this->key, $this->item) = array($this->item, $this->key);
        }
        $this->iteratable = symbol($this->iteratable);
        $this->reversed = (bool) $this->reversed;
    }

    public function render($context, $stream) {
        $iteratable = $context->resolve($this->iteratable);

        if ($this->reversed)
            $iteratable = array_reverse($iteratable);

        if ($this->limit)
            $iteratable = array_slice($iteratable, 0, $this->limit);

        $length = (is_array($iteratable) ? count($iteratable) : 0);

        if ($length) {
            $parent = $context['loop'];
            $context->push();
            $rev_count = $is_even = $idx = 0;
            foreach($iteratable as $key => $value) {
                $is_even =  $idx % 2;
                $rev_count = $length - $idx;

                if ($this->key) {
                    $context[$this->key] = $key;
                }
                $context[$this->item] =  $value;
                $context['loop'] = array(
                    'parent' => $parent,
                    'first' => $idx === 0,
                    'last'  => $rev_count === 1,
                    'odd'   => !$is_even,
                    'even'  => $is_even,
                    'length' => $length,
                    'counter' => $idx + 1,
                    'counter0' => $idx,
                    'revcounter' => $rev_count,
                    'revcounter0' => $rev_count - 1
                );
                $this->body->render($context, $stream);
                ++$idx;
            }
            $context->pop();
        } elseif ($this->else)
            $this->else->render($context, $stream);
    }
}

class Block_Tag extends H2o_Node {
    public $name;
    public $position;
    public $stack;
    private $syntax = '/^[a-zA-Z_][a-zA-Z0-9_-]*$/';

    public function __construct($argstring, $parser, $position) {
        if (!preg_match($this->syntax, $argstring))
            throw new TemplateSyntaxError('Block tag expects a name, example: block [content]');

        $this->name = $argstring;

        if (isset($parser->storage['blocks'][$this->name]))
            throw new TemplateSyntaxError('Block name exists, Please select a different block name');

        $this->filename = $parser->filename;
        $this->stack = array($parser->parse('endblock', "endblock {$this->name}"));

        $parser->storage['blocks'][$this->name] = $this;
        $this->position = $position;
    }

    public function addLayer(&$nodelist) {
        $nodelist->parent = $this;
        $this->stack[] = $nodelist;
    }

    public function render($context, $stream, $index = 1) {
        $key = count($this->stack) - $index;

        if (isset($this->stack[$key])) {
            $context->push();
            $context['block'] = new BlockContext($this, $context, $index);
            $this->stack[$key]->render($context, $stream);
            $context->pop();
        }
    }
}

class Extends_Tag extends H2o_Node {
    public $filename;
    public $position;
    public $nodelist;
    private $syntax = '/^["\'](.*?)["\']$/';

    public function __construct($argstring, $parser, $position = 0) {
      if (!$parser->first)
            throw new TemplateSyntaxError('extends must be first in file');

      if (!preg_match($this->syntax, $argstring))
            throw new TemplatesyntaxError('filename must be quoted');

        $this->filename = h2o::getFilenameWithGlobal(stripcslashes(substr($argstring, 1, -1)));

        # Parse the current template
        $parser->parse();

        # Parse parent template
        $this->nodelist = $parser->runtime->loadSubTemplate($this->filename, $parser->options);
        $parser->storage['templates'] = array_merge(
            $parser->storage['templates'], $this->nodelist->parser->storage['templates']
        );
        $parser->storage['templates'][] = $this->filename;

        if (!isset($this->nodelist->parser->storage['blocks']) || !isset($parser->storage['blocks']))
            return ;

        # Blocks of parent template
        $blocks =& $this->nodelist->parser->storage['blocks'];

        # Push child blocks on top of parent blocks
        foreach($parser->storage['blocks'] as $name => &$block) {
            if (isset($blocks[$name])) {
                $blocks[$name]->addLayer($block);
            }
        }
    }

    public function render($context, $stream) {
        $this->nodelist->render($context, $stream);
    }
}

/**
 * include tag
 *
 * Usage:
 *
 * Simple inclusion
 *     {% include "./subtemplate.html" %}
 *
 *
 * Inclusion with additional context variables passing:
 *     {% include "./subtemplate.html" with foo=bar spam="eggs" %}
 *
 * Note: Double quotes matter. In this example 'foo' template variable of subtemplate.html
 * would be initialized with 'bar' variable contents (from main template context),
 * while 'spam' template variable of subtemplate.html would be set to simple string ('eggs').
 *
 */
class Include_Tag extends H2o_Node {
    private $nodelist;
    private $syntax = '/^["\'](.*?)["\'](\s+with\s+(.+))?$/';
    private $_additional_context = array();

    public function __construct($argstring, $parser, $position = 0) {
        $matches = [];
        if (!preg_match($this->syntax, $argstring, $matches)) {
            throw new TemplateSyntaxError();
        }

        $matches_count = count($matches);

        if ($matches_count > 2) {
            // "with" statement supplied.
            $tmp = explode(' ', $matches[3]);
            $text=false;
            $with_vars = [];
            
            $index = count($tmp);
            for($i=0 ; $i < $index; $i++){
                if($text === false && preg_match('/^\w+=["]/',$tmp[$i])){
                    if(substr($tmp[$i],-1) === '"'){
                        $with_vars[] = $tmp[$i];
                    } else {
                        // Text d�coup� par l'explode
                    	$text = true;
                    	$current = $tmp[$i];
                    }
                }else{
                    if($text === false){
                        $with_vars[] = $tmp[$i];
                    }else{
                        $current.= " ".$tmp[$i];
                        if(substr($tmp[$i],-1) === '"'){
                            $text = false;
                            $with_vars[] = $current;
                        }
                    }
                }
            }
            foreach ($with_vars as $var_str) {
                $eq_pos = strpos($var_str, '=');
                $this->_additional_context[substr($var_str, 0, $eq_pos)] = substr($var_str, $eq_pos+1);
            }
        }

        $this->filename = h2o::getFilenameWithGlobal(stripcslashes($matches[1]));
        $this->nodelist = $parser->runtime->loadSubTemplate($this->filename, $parser->options);
        $parser->storage['templates'] = array_merge(
            $this->nodelist->parser->storage['templates'], $parser->storage['templates']
        );
        $parser->storage['templates'][] = $this->filename;
    }

    public function render($context, $stream) {
        foreach ($this->_additional_context as $key => $value) {
            switch (substr($value, 0, 1)) {
                case '"':
                case '`':
                case "'":
                    $value = trim($value, '"');
                    break;
                default:
                    // Context variable supplied as value. Needs to be resolved.
                    $value = $context->getVariable($value);
                    break;
            }
            $context[$key] = $value;
        }
        $this->nodelist->render($context, $stream);
    }
}

class With_Tag extends H2o_Node {
    public $position;
    private $variable, $shortcut;
    private $nodelist;
    private $syntax = '/^([\w]+(:?\.[\w\d]+)*)\s+as\s+([\w]+(:?\.[\w\d]+)?)$/';

    public function __construct($argstring, $parser, $position = 0) {
        $matches = [];
        if (!preg_match($this->syntax, $argstring, $matches))
            throw new TemplateSyntaxError('Invalid with tag syntax');

        # extract the long name and shortcut
        list(,$this->variable, ,$this->shortcut) = $matches;
        $this->nodelist = $parser->parse('endwith');
    }

    public function render($context, $stream) {
        $variable = $context->getVariable($this->variable);

        $context->push(array($this->shortcut => $variable));
        $this->nodelist->render($context, $stream);
        $context->pop();
    }
}

class Cycle_Tag extends H2o_Node {
    private $uid;
    private $sequence;

    public function __construct($argstring, $parser, $pos) {
        $args = h2o_parser::parseArguments($argstring);

        if (count($args) < 2) {
            throw new Exception('Cycle tag require more than two items');
        }
        $this->sequence = $args;
        $this->uid = '__cycle__'.$pos;
    }

    public function render($context, $stream) {
        if (!is_null($item = $context->getVariable($this->uid))) {
            $item = ($item + 1) % count($this->sequence);
        } else {
            $item = 0;
        }
        $stream->write($context->resolve($this->sequence[$item]));
        $context->set($this->uid, $item);
    }
}

class Load_Tag extends H2o_Node {
    public $position;
    private $searchpath = array(H2O_ROOT);
    private $extension;

    public function __construct($argstring, $parser, $pos = 0) {
        $this->extension = stripcslashes(preg_replace("/^[\"'](.*)[\"']$/", "$1", $argstring));

        if ($parser->runtime->searchpath)
            $this->appendPath($parser->runtime->searchpath);

        $parser->storage['included'][$this->extension] = $this->load();
        $this->position = $pos;
    }

    public function render($context, $stream) {
        $this->load();
    }

    public function appendPath($path) {
        $this->searchpath[] = $path;
    }

    private function load() {
        if (isset(h2o::$extensions[$this->extension])) {
            return true;
        }
        foreach($this->searchpath as $path) {
            $file = $path.'ext'.DS.$this->extension.'.php';
            if (is_file($file)) {
                h2o::load($this->extension, $file);
                return $file;
            }
        }
        throw new H2o_Error(
            "Extension: {$this->extension} cannot be loaded, please confirm it exist in extension path"
        );
    }
}

class Debug_Tag extends H2o_Node {
    private $argument;
    public function __construct($argstring, $parser, $pos = 0) {
        $this->argument = $argstring;
    }

    public function render($context, $stream) {
    	global $charset;
        if ($this->argument) {
            $object = $context->resolve(symbol($this->argument));
        } else {
            $object = $context->scopes[0];
        }
        $output = "<pre>" . htmlspecialchars(print_r($object, true), ENT_QUOTES | ENT_IGNORE, $charset) . "</pre>";
        $stream->write($output);
    }
}

class Comment_Tag extends H2o_Node {
    public function __construct($argstring, $parser, $position) {
        $parser->parse('endcomment');
    }

    public function render($context, $stream, $index = 1) {
    }
}

class Now_Tag extends H2o_Node {
    public function __construct($argstring, $parser, $pos=0) {
        $this->format = $argstring;
        if (!$this->format) {
            $this->format = "D M j G:i:s T Y";
        }
    }

    public function render($contxt, $stream) {
        $time = date($this->format);
        $stream->write($time);
    }
}

class Autoescape_Tag extends H2o_Node {
    protected $enable;

    public function __construct($argstring, $parser, $pos = 0) {
        if ($argstring === 'on')
            $this->enable = true;
        elseif ($argstring === 'off')
            $this->enable = false;
        else throw new H2o_Error(
            "Invalid syntax : autoescape on|off "
        );
    }

    public function render($context, $stream) {
        $context->autoescape = $this->enable;
    }
}

class Csrf_token_Tag extends H2o_Node {
    public function render($context, $stream) {
        global $charset;
        
        $token = "";
        if (isset($_COOKIE["csrftoken"])) {
            $token = $_COOKIE["csrftoken"];
        } else {
            $token = md5(mt_rand());
            if (defined('SECRET_KEY')) {
                $token = md5(mt_rand() . SECRET_KEY);
            }
        }
        setcookie("csrftoken", $token, time() + 60 * 60 * 24 * 365, "/");
        $stream->write("<div style='display:none'><input type=\"hidden\" value=\"".htmlentities($token, ENT_QUOTES, $charset)."\" name=\"csrfmiddlewaretoken\" /></div>");
    }
}

H2o::addTag(array('block', 'extends', 'include', 'if', 'ifchanged', 'for', 'with', 'cycle', 'load', 'debug', 'comment', 'now', 'autoescape', 'csrf_token'));
