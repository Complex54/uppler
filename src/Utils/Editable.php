<?php

namespace App\Utils;

use Symfony\Component\HttpFoundation\Request;


interface Editable

{

  public function edit(Request $request, $id);

  public function delete($id);

}