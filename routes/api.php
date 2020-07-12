<?php

use Framework\Routes;

Routes::get("task", function () {

});

Routes::get("task/{id}", function ($id) {
    echo "CCCCCCCCCCCCCC";
});

Routes::post("task", function () {
    echo "BBBBBBBBBBBBBBBBB";
});
