<?php
/**
 * template files are a mix of html, php and javascript
 */
?>
<div class="accounts" style="width:800px;">
    <div class="navbar">
        <div class="navbar-inner">
        <ul class="nav">
            <a class="brand" href="#">Actions</a>
            <li><?php echo $this->Html->link(__('New Account'), array('action' => 'add')); ?></li>
    		<li><?php echo $this->Html->link(__('List Customers'), array('controller' => 'customers', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Customer'), array('controller' => 'customers', 'action' => 'add')); ?> </li>
        </ul>
        </div>
        <?php echo '<script>alert(Bancha.t("This is some string inside a php code"))</script>'; ?>
    </div>

    <script>
    	// Sometimes the javascript can be not really clean as well and broken into parts, like here
    	// // People should never do things like this, but there still might be legacy code like this :-/
    	<?php
    		echo $someVar;

    		if($someBoolean) {
    			echo ' }, function() { return Bancha.t("This is a string in a partial javascript code"); }';
    		}
    	?>
    	});
	</script>
</div>