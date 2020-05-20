<?php

namespace Alura\Leilao\Tests\Service;

use Alura\Leilao\Dao\Leilao as LeilaoDao;
use Alura\Leilao\Model\Leilao;
use Alura\Leilao\Service\Encerrador;
use Alura\Leilao\Service\EnviadorEmail;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class EncerradorTest extends TestCase
{
    private Encerrador $encerrador;
    private MockObject $enviadorEmailMock;
    private Leilao $leilaoFiat147;
    private Leilao $leilaoVariant;

    public function setUp(): void
    {
        $this->leilaoFiat147 = new Leilao(
            'Fiat 147 0Km',
            new \DateTimeImmutable('8 days ago')
        );

        $this->leilaoVariant = new Leilao(
            'Variant 1972 0Km',
            new \DateTimeImmutable('10 days ago')
        );

        $leilaoDao = $this->createMock(LeilaoDao::class);
        /*
        $leilaoDao = $this->getMockBuilder(LeilaoDao::class)
            ->setConstructorArgs([new \PDO('sqlite::memory:')])
            ->getMock();
        */
        $leilaoDao->method('recuperarNaoFinalizados')
            ->willReturn([$this->leilaoFiat147, $this->leilaoVariant]);
        $leilaoDao->method('recuperarFinalizados')
            ->willReturn([$this->leilaoFiat147, $this->leilaoVariant]);
        $leilaoDao->expects($this->exactly(2))
            ->method('atualiza')
            ->withConsecutive(
                [$this->leilaoFiat147],
                [$this->leilaoVariant]
            );

        $this->enviadorEmailMock = $this->createMock(EnviadorEmail::class);

        $this->encerrador = new Encerrador($leilaoDao, $this->enviadorEmailMock);
    }

    public function testLeiloesComMaisDeUmaSemanaDevemSerEncerrados()
    {
        $this->encerrador->encerra();
        $leiloes = [$this->leilaoFiat147, $this->leilaoVariant];

        $this->assertTrue($leiloes[0]->estaFinalizado());
        $this->assertTrue($leiloes[1]->estaFinalizado());
        $this->assertEquals('Fiat 147 0Km', $leiloes[0]->recuperarDescricao());
        $this->assertEquals('Variant 1972 0Km', $leiloes[1]->recuperarDescricao());
    }

    public function testDeveContinuarOProcessamentoAoEncontrarErroAoEnviarEmail()
    {
        $e = new \DomainException('Erro ao enviar e-mail');
        $this->enviadorEmailMock->expects($this->exactly(2))
            ->method('notificarTerminoLeilao')
            ->willThrowException($e);

        $this->encerrador->encerra();
    }

    public function testSoDeveEnviarLeilaoPorEmailAposFinalizado()
    {
        $this->enviadorEmailMock->expects($this->exactly(2))
            ->method('notificarTerminoLeilao')
            ->willReturnCallback(
                function (Leilao $leilao) {
                    $this->assertTrue($leilao->estaFinalizado());
            }
            );

        $this->encerrador->encerra();
    }

}