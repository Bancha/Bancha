Bancha Update Info
==================

Upgrading from 1.3 to 2.0
-------------------------

The whole API stayed the same, only the javascript functions Bancha.getModel()
and Bancha.onModelReady() got deprecated in favor of Ext.require or the
require property in class definitions.

Upgrading from 1.2 to 2.0
-------------------------

From 1.2 to 1.3 the Controller Method Return Values changed a little:

Bancha now follows a more straight forward way of how to determin if the controllers result value
is the data portion of the response or the whole response:

 - If a result has a success property it will be traited as a final response and will not be transformed.
 - Otherwise it will be transformed, folowed by the rules which can be found at:
 [Controller Method Results Documentation](http://docs.banchaproject.org/resources/Supported-Controller-Method-Results.html)

