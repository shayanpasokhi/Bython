<?php

define('ID', 'ID');
define('ASSIGN', 'ASSIGN');
define('NUMBER', 'NUMBER');
define('PLUS', 'PLUS');
define('MINUS', 'MINUS');
define('LPAREN', 'LPAREN');
define('RPAREN', 'RPAREN');
define('COMMA', 'COMMA');
define('COLON', 'COLON');
define('VOROODI', 'VOROODI');
define('KHOOROOJI', 'KHOOROOJI');
define('AGAR', 'AGAR');
define('EQUAL', 'EQUAL');
define('SEMI', 'SEMI');
define('EOF', 'EOF');

class Token{
    public $type;
    public $value;

    public function __construct($type, $value){
        $this->type = $type;
        $this->value = $value;
    }

    public function __tostring(){
        return "Token(" . $this->type . ", " . var_export($this->value, true) . ")";
    }
}

$RESERVED_KEYWORD = [
    strtolower(VOROODI) => new Token(VOROODI, strtolower(VOROODI)),
    strtolower(KHOOROOJI) => new Token(KHOOROOJI, strtolower(KHOOROOJI)),
    strtolower(AGAR) => new Token(AGAR, strtolower(AGAR))
];

class Lexer{
    public $text;
    public $pos;
    public $currentChar;
    public $RESERVED_KEYWORD;

    public function __construct($text, $RESERVED_KEYWORD){
        $this->text = $text;
        $this->pos = 0;
        $this->currentChar = $this->text[$this->pos];
        $this->RESERVED_KEYWORD = $RESERVED_KEYWORD;
    }

    public function error($message = 'Invalid character'){
        echo $message;
        exit;
    }

    public function advance(){
        ++$this->pos;

        if($this->pos > strlen($this->text) - 1){
            $this->currentChar = null;
        }else{
            $this->currentChar = $this->text[$this->pos];
        }
    }

    public function number(){
        $result = '';

        while(!is_null($this->currentChar) && ctype_digit($this->currentChar)){
            $result .= $this->currentChar;
            $this->advance();
        }

        return (int)$result;
    }

    public function skipWhitespace(){
        while(!is_null($this->currentChar) && ctype_space($this->currentChar)){
            $this->advance();
        }
    }

    public function id(){
        $result = '';

        while(!is_null($this->currentChar) && ctype_alnum($this->currentChar)){
            $result .= $this->currentChar;
            $this->advance();
        }

        return isset($this->RESERVED_KEYWORD[$result]) ? $this->RESERVED_KEYWORD[$result] : new Token(ID, $result);
    }

    public function getNextToken(){
        while(!is_null($this->currentChar)){
            if(ctype_space($this->currentChar)){
                $this->skipWhitespace();

                continue;
            }

            if($this->currentChar == ';'){
                $this->advance();

                return new Token(SEMI, ';');
            }

            if(ctype_digit($this->currentChar)){
                return new Token(NUMBER, $this->number());
            }

            if(ctype_alpha($this->currentChar)){
                return $this->id();
            }

            if($this->currentChar == '='){
                $this->advance();

                if($this->currentChar == '='){
                    $this->advance();
                    
                    return new Token(EQUAL, '==');
                }

                return new Token(ASSIGN, '=');
            }

            if($this->currentChar == '+'){
                $this->advance();

                return new Token(PLUS, '+');
            }

            if($this->currentChar == '-'){
                $this->advance();

                return new Token(MINUS, '-');
            }

            if($this->currentChar == '('){
                $this->advance();

                return new Token(LPAREN, '(');
            }

            if($this->currentChar == ')'){
                $this->advance();

                return new Token(RPAREN, ')');
            }

            if($this->currentChar == ','){
                $this->advance();

                return new Token(COMMA, ',');
            }

            if($this->currentChar == ':'){
                $this->advance();

                return new Token(COLON, ':');
            }

            $this->error();
        }

        return new Token(EOF, null);
    }
}

class AST{

}

class Compound extends AST{
    public $children;

    public function __construct(){
        $this->children = [];
    }
}

class Assign extends AST{
    public $left;
    public $op;
    public $right;

    public function __construct($left, $op, $right){
        $this->left = $left;
        $this->op = $op;
        $this->right = $right;
    }
}

class _Var extends AST{
    public $token;

    public function __construct($token){
        $this->token = $token;
    }
}

class Num extends AST{
    public $token;

    public function __construct($token){
        $this->token = $token;
    }
}

class BinOp extends AST{
    public $left;
    public $op;
    public $right;

    public function __construct($left, $op, $right){
        $this->left = $left;
        $this->op = $op;
        $this->right = $right;
    }
}

class Input extends AST{
    public $token;

    public function __construct($token){
        $this->token = $token;
    }
}

class _Print extends AST{
    public $token;
    public $list;

    public function __construct($token){
        $this->token = $token;
        $this->list = [];
    }
}

class _If extends AST{
    public $token;
    public $condition;
    public $op;
    public $assignment;

    public function __construct($token, $condition, $op, $assignment){
        $this->token = $token;
        $this->condition = $condition;
        $this->op = $op;
        $this->assignment = $assignment;
    }
}

class Condition extends AST{
    public $left;
    public $op;
    public $right;

    public function __construct($left, $op, $right){
        $this->left = $left;
        $this->op = $op;
        $this->right = $right;
    }
}

class NoOp extends AST{

}

class Parser{
    public $lexer;
    public $currentToken;

    public function __construct($lexer){
        $this->lexer = $lexer;
        $this->currentToken = $this->lexer->getNextToken();
    }

    public function error($message = 'Invalid syntax'){
        echo $message;
        exit();
    }

    public function eat($tokenType){
        if($this->currentToken->type == $tokenType){
            $this->currentToken = $this->lexer->getNextToken();
        }else{
            $this->error();
        }
    }

    public function program(){
        return $this->compoundStatement();
    }

    public function compoundStatement(){
        $node = $this->statementList();
        $result = new Compound();

        foreach($node as $item){
            $result->children[] = $item;
        }

        return $result;
    }

    public function statementList(){
        $node = $this->statement();
        $result = [$node];

        while($this->currentToken->type == SEMI){
            $this->eat(SEMI);
            $result[] = $this->statement();
        }

        return $result;
    }

    public function statement(){
        if($this->currentToken->type == ID){
            $node = $this->assignment();
        }elseif($this->currentToken->type == VOROODI){
            $node = $this->input();
        }elseif($this->currentToken->type == KHOOROOJI){
            $node = $this->_print();
        }elseif($this->currentToken->type == AGAR){
            $node = $this->_if();
        }elseif($this->currentToken->type == SEMI){
            $node = $this->empty();
        }else{
            $this->error();
        }

        return $node;
    }

    public function empty(){
        return new NoOp();
    }

    public function assignment(){
        $left = $this->variable();
        $op = $this->currentToken;
        $this->eat(ASSIGN);

        return new Assign($left, $op, $this->expr());
    }

    public function variable(){
        $token = $this->currentToken;
        $this->eat(ID);

        return new _Var($token);
    }

    public function expr(){
        if($this->currentToken->type == VOROODI){
            return $this->input();
        }else{
            $left = $this->arg();

            if(in_array($this->currentToken->type, [PLUS, MINUS])){
                $op = $this->currentToken;

                if($this->currentToken->type == PLUS){
                    $this->eat(PLUS);
                }else{
                    $this->eat(MINUS);
                }

                return new BinOp($left, $op, $this->arg());
            }

            return $left;
        }
    }

    public function input(){
        $token = $this->currentToken;
        $this->eat(VOROODI);
        $this->eat(LPAREN);
        $this->eat(RPAREN);

        return new Input($token);
    }

    public function arg(){
        if($this->currentToken->type == NUMBER){
            $token = $this->currentToken;
            $this->eat(NUMBER);

            return new Num($token);
        }else{
            return $this->variable();
        }
    }

    public function _print(){
        $token = $this->currentToken;
        $this->eat(KHOOROOJI);
        $this->eat(LPAREN);
        $_print = new _Print($token);
        $_print->list = $this->list();
        $this->eat(RPAREN);
        
        return $_print;
    }

    public function list(){
        $node = $this->arg();
        $result = [$node];

        while($this->currentToken->type == COMMA){
            $this->eat(COMMA);
            $result[] = $this->arg();
        }

        return $result;
    }

    public function _if(){
        $token = $this->currentToken;
        $this->eat(AGAR);
        $condition = $this->condition();
        $op = $this->currentToken;
        $this->eat(COLON);

        return new _If($token, $condition, $op, $this->assignment());
    }

    public function condition(){
        $left = $this->arg();
        $op = $this->currentToken;
        $this->eat(EQUAL);

        return new Condition($left, $op, $this->arg());
    }

    public function parse(){
        $node = $this->program();

        if($this->currentToken->type != EOF){
            $this->error();
        }

        return $node;
    }
}

class NodeVisitor{
    public function visit($node){
        $methodName = 'visit' . get_class($node);

        if(method_exists(get_class($this), $methodName)){
            return $this->{$methodName}($node);
        }else{
            $this->genericVisit($node);
        }
    }

    public function genericVisit($node){
        echo 'No visit' . get_class($node) . ' method';
        exit();
    }
}

class Interpreter extends NodeVisitor{
    public $GLOBAL_SCOPE;
    public $parser;
    public $input;

    public function __construct($parser, $input){
        $this->parser = $parser;
        $this->GLOBAL_SCOPE = [];
        $this->input = $input;
    }

    public function visitCompound($node){
        foreach($node->children as $item){
            $this->visit($item);
        }
    }

    public function visitAssign($node){
        $varName = $node->left->token->value;
        $this->GLOBAL_SCOPE[$varName] = $this->visit($node->right);
    }

    public function visit_Var($node){
        $varName = $node->token->value;
        
        if(isset($this->GLOBAL_SCOPE[$varName])){
            return $this->GLOBAL_SCOPE[$varName];
        }else{
            exit();
        }
    }

    public function visitNum($node){
        return $node->token->value;
    }

    public function visitBinOp($node){
        if($node->op->type == PLUS){
            return $this->visit($node->left) + $this->visit($node->right);
        }elseif($node->op->type == MINUS){
            return $this->visit($node->left) - $this->visit($node->right);
        }
    }

    public function visitInput($node){
        return array_shift($this->input);
    }

    public function visit_Print($node){
        $result = '';

        foreach($node->list as $item){
            $result .= $this->visit($item) . ' ';
        }

        echo rtrim($result, ' ') . PHP_EOL;
    }

    public function visit_If($node){
        if($this->visit($node->condition)){
            $this->visit($node->assignment);
        }
    }

    public function visitCondition($node){
        if($this->visit($node->left) == $this->visit($node->right)){
            return true;
        }else{
            return false;
        }
    }

    public function visitNoOp(){
        
    }

    public function interpret(){
        $tree = $this->parser->parse();

        if(is_null($tree)){
            return '';
        }

        return $this->visit($tree);
    }
}

function completePattern($pattern){
    return '/' . $pattern . '/';
}

function absPattern($pattern){
    return '^' . $pattern . '$';
}

function preAnalysis($text, $pattern){
    foreach($pattern as $item){
        if(preg_match(completePattern($item), $text)){
            return true;
        }
    }

    return false;
}

$text = [];
$input = [];
$inputCount = 0;

$assignPattern = '\ *\=\ *';
$equalPattern = '\ *\=\=\ *';
$numberPattern = '\ *\d+\ *';
$variablePattern = '\ *[a-zA-Z][a-zA-Z0-9]{0,9}\ *';
$argPattern = '\ *(' . $numberPattern . '|' . $variablePattern . ')\ *';
$inputPattern = '\ *voroodi\ *\(\ *\)\ *';
$listPattern = '\ *' . $argPattern . '\ *(\,\ *' . $argPattern . ')*\ *';
$printPattern = '\ *khoorooji\ *\(\ *' . $listPattern . '\ *\)\ *';
$conditionPattern = '\ *' . $argPattern . '\ *' . $equalPattern . '\ *' . $argPattern . '\ *';
$exprPattern = '\ *(' . $numberPattern . '|' . $inputPattern . '|' . $argPattern . '\ *(\+|\-)\ *' . $argPattern . ')\ *';
$assignmentPattern = '\ *' . $variablePattern . '\ *' . $assignPattern . '\ *' . $exprPattern . '\ *';
$ifPattern = '\ *agar\ *' . $conditionPattern . '\ *\:\ *' . $assignmentPattern . '\ *';

$pattern = array_map(fn($value) => absPattern($value), [$inputPattern, $printPattern, $ifPattern, $assignmentPattern]);

while(($line = readline()) != '-----'){
    if(!$line || !preAnalysis($line, $pattern)){
        continue;
    }

    if(preg_match(completePattern($inputPattern), $line)){
        ++$inputCount;
    }

    $text[] = $line;
}

for($i = 0; $i < $inputCount; $i++){
    $input[] = readline();
}

$lexer = new Lexer(implode(';', $text), $RESERVED_KEYWORD);
$parser = new Parser($lexer);
$interpreter = new Interpreter($parser, $input);
$interpreter->interpret();
echo count($interpreter->GLOBAL_SCOPE);
