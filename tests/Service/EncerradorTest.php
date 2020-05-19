<?php

namespace Alura\Leilao\Tests\Enerrador;

use Alura\Leilao\Dao\Leilao as LeilaoDao;
use Alura\Leilao\Model\Leilao;
use Alura\Leilao\Service\Encerrador;
use PHPUnit\Framework\TestCase;

class EncerradorTest extends TestCase
{
    public function testLeiloesComMaisDeUmaSemanaDevemSerEncerrados()
    {
        $fiat147 = new Leilao(
            'Fiat 147 0Km',
            new \DateTimeImmutable('8 days ago')
        );

        $variant = new Leilao(
            'Variant 1972 0Km',
            new \DateTimeImmutable('10 days ago')
        );

        $leilaoDao = new LeilaoDao();
        $leilaoDao->salva($fiat147);
        $leilaoDao->salva($variant);

        $encerrador = new Encerrador();
        $encerrador->encerra();

        $leiloes = $leilaoDao->recuperarFinalizados();

        $this->assertCount(2, $leiloes);
        $this->assertEquals('Fiat 147 0Km', $leiloes[0]->recuperarDescricao());
        $this->assertEquals('Variant 1972 0Km', $leiloes[1]->recuperarDescricao());
    }

}