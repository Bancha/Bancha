/*
 * This file simply triggeres an error which is used for checking if Bancha really catches errors and sends the stack trace
 */
 var a = undefined['This error should be thrown and will not be catched'];