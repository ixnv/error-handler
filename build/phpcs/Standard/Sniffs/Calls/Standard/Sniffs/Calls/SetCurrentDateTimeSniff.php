<?php

class Standard_Sniffs_Calls_SetCurrentDateTimeSniff implements PHP_CodeSniffer_Sniff
{
    public function register()
    {
        return [T_DOUBLE_COLON];
    }

    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        if ($tokens[$stackPtr + 1]['content'] == 'setCurrentSystemTime'
            && $tokens[$stackPtr - 1]['content'] == 'SystemDateTime'
        ) {

            $error = 'Method %s::%s should not be called in production code';
            $data = [
                trim($tokens[$stackPtr - 1]['content']),
                trim($tokens[$stackPtr + 1]['content']),
            ];
            $phpcsFile->addError($error, $stackPtr, 'Found', $data);
        }

    }
}
