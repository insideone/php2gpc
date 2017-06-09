<?php
namespace Inside\PhpToGpc;

use PhpParser\Node\Const_;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt;
use PhpParser\PrettyPrinter\Standard;
use PhpParser\Node\Scalar;
use PhpParser\Node\Expr\BinaryOp;

class Printer extends Standard
{
    /**
     * @var bool Режим сбора информации
     */
    protected $collectMode = true;

    /**
     * @var array Список функций которые будут записываться в сокращённой форме
     */
    protected $shortcuts = ['main', 'init'];

    /**
     * @var array Список переменных для объявления в начале скрипта. Собирается динамически
     */
    protected $variables = [];

    /**
     * @var array Список объявленных комбо и функций
     */
    protected $callables = [];

    public function prettyPrint(array $stmts)
    {
        // Первый раз нужно вызвать рендер впустую, т.к. требуется собрать список переменных, кобмо и т.п.
        parent::prettyPrint($stmts);
        $this->collectMode = false;
        
        $main = parent::prettyPrint($stmts);
        return 'int '.implode(', ', array_keys($this->variables)).";\n{$main}";
    }

    protected function pExpr_Variable(Expr\Variable $node)
    {
        if ($node->name instanceof Expr) {
            throw new NodeException('Expressions in variables not allowed', $node);
        } else {
            $this->variables[$node->name] = true;
            return $node->name;
        }
    }
    
    protected function pExpr_ConstFetch(Expr\ConstFetch $node)
    {
        $const = $this->p($node->name);
        if (in_array(strtolower($node->name), ['true', 'false'])) {
            $const = strtoupper($const);
        }
        return $const;
    }

    protected function pStmt_Function(Stmt\Function_ $node)
    {
        return $this->buildFunction($node->params, $node->getStmts(), $node->name);
    }

    protected function pStmt_Const(Stmt\Const_ $node)
    {
        return 'define ' . $this->pCommaSeparated($node->consts) . ';';
    }

    protected function buildFunction($params, $stmts, $name = null)
    {
        $isShort = $name ? in_array($name, $this->shortcuts) : true;

        $c = [];
        if (!$isShort) {
            $c[] = 'function ';
        }

        if ($name) {
            $c[] = $name;
            $this->callables[$name] = true;
        }

        if (!$isShort) {
            $c[] = '(' . $this->pCommaSeparated($params) . ') ';
        }

        $c[] = '{' . $this->pStmts($stmts) . "\n" . '}';


        return implode('', $c);
    }
    
    protected function pExpr_Closure(Expr\Closure $node)
    {
        return $this->buildFunction($node->params, $node->getStmts());
    }

    protected function pExpr_Assign(Expr\Assign $node)
    {
        if ($node->expr instanceof Expr\Closure) {
            if ($node->var instanceof Expr\Variable) {
                $this->callables[$node->var->name] = true;
            }
            return 'combo '.$this->p($node->var).' '.$this->p(new Expr\Closure(['stmts' => $node->expr->getStmts()]));
        } else {
            return $this->pInfixOp('Expr_Assign', $node->var, ' = ', $node->expr);
        }
    }
    
    protected function pScalar_String(Scalar\String_ $node)
    {
        if (isset($this->callables[$node->value]) || isset($this->variables[$node->value])) {
            return $node->value;
        }

        if (!$this->collectMode) {

            throw new NodeException("String are not allowed", $node);
        }
        
        return parent::pScalar_String($node);
    }
    
    protected function pExpr_FuncCall(Expr\FuncCall $node)
    {
        switch($fName = $node->name->getFirst()) {
            case 'define':
                $arg = $node->args[0]->value;

                $value = null;

                if ($arg instanceof Scalar\String_) {
                    $value = $arg->value;
                } elseif (property_exists($arg, 'name')) {
                    $value = $arg->{'name'};
                }

                return rtrim($this->pStmt_Const(new Stmt\Const_([
                    new Const_($value, $node->args[1]->value)
                ])), ';');
                break;

            case 'unmap':
            case 'remap':
                return $fName.' '.$this->pImplode($node->args, ' -> ');
                break;
        }

        return $this->pCallLhs($node->name)
        . '(' . $this->pCommaSeparated($node->args) . ')';
    }

    protected function pExpr_BinaryOp_Identical(BinaryOp\Identical $node)
    {
        return parent::pExpr_BinaryOp_Equal(new BinaryOp\Equal($node->left, $node->right));
    }
}