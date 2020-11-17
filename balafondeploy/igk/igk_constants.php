<?php
// author: C.A.D. BONDJE DOUE

///<summary>Represente class: IGKConstants</summary>
/**
* Represente IGKConstants class
*/
final class IGKConstants{
    const NAMESPACE="http://schema.igkdev.com";
    const STR_PAGE_TITLE="{0} - [ {1} ]";
}
///<summary>represent environment constant</summary>
/**
* represent environment constant
*/
class IGKEnvConst{
    const NotifyLogin="notify/app/login";
}
///<summary>represent environment key constant</summary>
/**
* represent environment key constant
*/
final class IGKEnvKeys{
    const CTRL_CONTEXT_SOURCE_VIEW_ARGS=self::CURRENT_CTRL + 2;
    const CTRL_CONTEXT_VIEW_ARGS=self::CURRENT_CTRL + 1;
    const CURRENT_CTRL=0xE0;
    const VIEW_CURRENT_ACTION=self::CURRENT_CTRL + 3;
    const VIEW_HANDLE_ACTIONS=self::CURRENT_CTRL + 4;
    const VIEW_INC_VIEW=self::CURRENT_CTRL + 5;
}
///<summary>Represente class: IGKFieldConstants</summary>
/**
* Represente IGKFieldConstants class
*/
final class IGKFieldConstants{
    const FirstName=self::Prefix."FirstName";
    const LastName=self::Prefix."LastName";
    const Login=self::Prefix."Login";
    const Prefix="cl";
}
