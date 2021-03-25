<?php

namespace Mustafa\Htmlbank\Model\Rewrite;

class InstructionsConfigProvider extends \Magento\OfflinePayments\Model\InstructionsConfigProvider

{
    public function getInstructions($code)
    {
        if($code == 'banktransfer'){//check payment method is banktransfer
            return nl2br($this->methods[$code]->getInstructions());// removed escapeHtml function!
        }else{
            return nl2br($this->escaper->escapeHtml($this->methods[$code]->getInstructions()));
        }
    }

}
