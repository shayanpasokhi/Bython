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
