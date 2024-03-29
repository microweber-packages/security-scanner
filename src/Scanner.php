<?php

namespace MicroweberPackages\SecurityScanner;

use PhpParser\Node;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\NodeVisitorAbstract;

class Scanner
{
    public $notAllowedPhpFunctions = [
        'eval',
        'system',
        'passthru',
      //  'getenv',
        'popen',
        'pcntl_exec',
        'proc_open',
        'proc_nice',
        'proc_terminate',
        'proc_close',
        'pfsockopen',
        'fsockopen',
        'apache_child_terminate',
        'posix_kill',
        'posix_mkfifo',
        'posix_setpgid',
        'posix_setsid',
        "getcwd",
        "getlastmo",
        "getmygid",
        "getmyinode",
        "getmypid",
        "getmyuid",
        "str_rot13",
        "strrev",
        "gzinflate",
        "gzuncompress",
    ];
    private $_foundedPhpFunctions = [];

    public function scanFile($file)
    {
        $content = file_get_contents($file);
        return $this->scanString($content);
    }

    public function scanString($content) {

        $parser = (new \PhpParser\ParserFactory())->create(\PhpParser\ParserFactory::PREFER_PHP7);
        try {
            $ast = $parser->parse($content);
        } catch (Error $error) {
            echo "Parse error: {$error->getMessage()}\n";
            return ['error' => true, 'message' => 'Invalid file syntax'];
        }

        $nodeFinder = new \PhpParser\NodeFinder();

        // Find all functions nodes.
        $functions = $nodeFinder->findInstanceOf($ast, Function_::class);
        $expressionFunctions = $nodeFinder->findInstanceOf($ast, Expression::class);
        $funcCall = $nodeFinder->findInstanceOf($ast, FuncCall::class);
        $allFunctions = array_merge_recursive($functions, $expressionFunctions, $funcCall);

        print_r($allFunctions);

        if (!empty($allFunctions)) {
            foreach ($allFunctions as $function) {
                if (isset($function->expr->name->parts)) {
                    if (is_array($function->expr->name->parts)) {
                        foreach ($function->expr->name->parts as $part) {
                            $this->_foundedPhpFunctions[] = $part;
                        }
                    }
                } else if (isset($function->expr->expr->name->parts)) {
                    if (is_array($function->expr->expr->name->parts)) {
                        foreach ($function->expr->expr->name->parts as $part) {
                            $this->_foundedPhpFunctions[] = $part;
                        }
                    }
                } else if (isset($function->name->parts)) {
                    if (is_array($function->name->parts)) {
                        foreach ($function->name->parts as $part) {
                            $this->_foundedPhpFunctions[] = $part;
                        }
                    }
                } else if (isset($function->name->name)) {
                    $this->_foundedPhpFunctions[] = $function->name->name;
                }
            }
        }

        $warnings = [];
        if (!empty($this->_foundedPhpFunctions)) {
            $this->_foundedPhpFunctions = array_unique($this->_foundedPhpFunctions);
            foreach ($this->_foundedPhpFunctions as $foundedPhpFunction) {
                if (in_array($foundedPhpFunction, $this->notAllowedPhpFunctions)) {
                    $warnings[] = $foundedPhpFunction;
                    break;
                }
            }
        }

        if (!empty($warnings)) {
            return ['error' => true, 'message' => 'File is corrupted', 'warnings' => $warnings];
        }

        return ['error' => false, 'message' => 'File is ok', 'warnings' => []];

    }

}
