<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../includes/score_validation.php';

final class ScoreValidationTest extends TestCase
{
    // Un score valide, réutilisé et modifié dans chaque test
    private function validInput(): array
    {
        return ['first_name' => 'Augustin', 'last_name' => 'Test', 'final_time' => '01:23:456', 'stopped_at' => 5, 'photo' => ''];
    }

    public function testConvertitUnTempsEnMillisecondes(): void
    {
        // 1 min + 23 s + 456 ms = 83 456 ms
        $this->assertSame(83456, parseFinalTime('01:23:456'));
    }

    public function testRefuseUnTempsMalFormate(): void
    {
        $this->assertNull(parseFinalTime('01:60:000')); // 60 secondes : invalide
    }

    public function testUnScoreValideEstAccepte(): void
    {
        $this->assertNull(validateScoreInput($this->validInput())['error']);
    }

    public function testUnChampManquantEstRefuse(): void
    {
        $input = $this->validInput();
        unset($input['last_name']);
        $this->assertSame('Tous les champs sont obligatoires.', validateScoreInput($input)['error']);
    }

    public function testUnPrenomTropLongEstRefuse(): void
    {
        $input = $this->validInput();
        $input['first_name'] = str_repeat('a', 81);
        $this->assertNotNull(validateScoreInput($input)['error']);
    }

    public function testUneEtapeHorsLimitesEstRefusee(): void
    {
        $input = $this->validInput();
        $input['stopped_at'] = 6;
        $this->assertNotNull(validateScoreInput($input)['error']);
    }

    public function testUnFichierQuiNestPasUneImageEstRefuse(): void
    {
        $this->assertFalse(isValidPhoto('data:image/png;base64,' . base64_encode('pas une image')));
    }
}