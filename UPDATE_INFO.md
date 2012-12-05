Bancha Update Info
==================

Following changes need to be kept in mind when upgrading from 1.2 to 1.3
------------------------------------------------------------------------

Controller Method Return Value
------------------------------

Bancha now follows a more straight forward way of how to determin if the controllers result value
is the data portion of the response or the whole response:  

 - If a result has a success property it will be traited as a final response and will not be transformed.
 - Otherwise it will be transformed, folowed by the rules which can be found at:
 [Controller Method Results Documentation](http://docs.banchaproject.org/resources/Supported-Controller-Method-Results.html)

