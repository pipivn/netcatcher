<div id="login_form">

    <form method="post" class="niceform">
    	<fieldset>
    		<dl>
            	<dt><label for="username" >Username</label></dt>
            	<dd class="row">
            	    <input type="text" name="username" value="<?php echo $login_name;?>"/>
                </dd>    
            </dl>
            <dl>
            	<dt><label for="password">Password</label></dt>
            	<dd class="row">
            		<input type="password" name="password" value=""/>
            	</dd>
            </dl>
            <dl><dt></dt><dd></dd></dl>
            <dl>
            	<dt></dt>
            	<dd>
            		<input type="submit" value="Login"/>
            	</dd>
            </dl>
        </fieldset>
    </form>
</div>