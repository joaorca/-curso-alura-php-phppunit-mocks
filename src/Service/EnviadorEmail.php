<?php

namespace Alura\Leilao\Service;


use Alura\Leilao\Model\Leilao;

class EnviadorEmail
{

    public function notificarTerminoLeilao(Leilao $leilao)
    {
        $success = mail(
            'usuario@email.com',
            'Leilão Finalizao',
            'O leilão para ' . $leilao->recuperarDescricao() . ' foi finalizado'
        );

        if (!$success) {
            throw new \DomainException('Erro ao enviar o email');
        }
    }

}