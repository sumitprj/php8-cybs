<?php

namespace CyberSource\Recaptcha\Model;

interface IsCheckRequiredInterface
{
    /**
     * Return true if check is required
     * @return bool
     */
    public function execute();
}
