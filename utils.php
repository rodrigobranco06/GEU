<?php 
    // utils.php 

    // Para facilitar o debug das variáveis 
    function show_var( $var, $var_name ) 
    {
        echo "<section>";
        echo "<em>show_var:</em><strong>$var_name</strong>";
        echo "<pre>";
        print_r( $var );
        echo "</pre>";
        echo "</section>";
    }

?>