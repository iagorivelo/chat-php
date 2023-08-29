<?php

namespace app\chat\View\partials;

class PartialControl
{
  public function render(string $partialName, array $data = NULL)
  {
    if (isset($data) && !empty($data)) {
      extract($data);
    }

    include __DIR__ . "/../" . $partialName . '.phtml';

    return ob_get_clean();
  }

  public function formatarDataEnvido($dt_envio)
  {

    $hoje = date('Y-m-d');

    $data = substr($dt_envio, 0, 10);

    if ($data == $hoje) {
      $hora = substr($dt_envio, 11, 5);
      return $hora;
    } else {
      $data_formatada = date('d/m/Y', strtotime($data));
      return $data_formatada;
    }
  }
}
