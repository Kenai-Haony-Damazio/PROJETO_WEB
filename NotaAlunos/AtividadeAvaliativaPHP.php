<?php
/**
 * Aplicação de análise de desempenho escolar
 * Funciona sem banco de dados, processando os dados via POST.
 */

// Funções auxiliares
function calcularMedia(float $n1, float $n2, float $trab): float {
    return ($n1 + $n2 + $trab) / 3;
}

function raizSomaNotas(float $n1, float $n2, float $trab): float {
    return sqrt($n1 + $n2 + $trab);
}

function diferencaAbsoluta(float $n1, float $n2, float $trab): float {
    $maior = max($n1, $n2, $trab);
    $menor = min($n1, $n2, $trab);
    return abs($maior - $menor);
}

function situacaoAluno(float $media): string {
    if ($media >= 7.0) {
        return 'Aprovado';
    } elseif ($media >= 5.0) {
        return 'Recuperação';
    } else {
        return 'Reprovado';
    }
}

// Inicialização da etapa
$etapa = isset($_POST['etapa']) ? (int)$_POST['etapa'] : 1;
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Análise de Desempenho Escolar</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 800px; margin: auto; }
        table { border-collapse: collapse; width: 100%; margin: 20px 0; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
        th { background: #f4f4f4; }
        .aprovado { color: green; font-weight: bold; }
        .recuperacao { color: orange; font-weight: bold; }
        .reprovado { color: red; font-weight: bold; }
        .mensagem { padding: 10px; border-radius: 5px; margin-top: 15px; }
        .sucesso { background: #d4edda; color: #155724; }
        .alerta { background: #fff3cd; color: #856404; }
        .erro { background: #f8d7da; color: #721c24; }
        input[type="text"], input[type="number"] { padding: 5px; margin: 2px; }
        button { padding: 10px 20px; margin-top: 10px; }
    </style>
</head>
<body>
<div class="container">
    <h1>Análise de Desempenho Escolar</h1>

    <?php if ($etapa === 1): ?>
        <!-- Etapa 1: Dados iniciais -->
        <form method="post" action="">
            <input type="hidden" name="etapa" value="2">
            <p>
                <label>Nome da Turma:</label><br>
                <input type="text" name="nome_turma" required>
            </p>
            <p>
                <label>Quantidade de Alunos:</label><br>
                <input type="number" name="qtd_alunos" min="1" max="50" required>
            </p>
            <button type="submit">Avançar</button>
        </form>
    <?php elseif ($etapa === 2): ?>
        <!-- Etapa 2: Cadastro das notas dos alunos -->
        <?php
        $nomeTurma = htmlspecialchars($_POST['nome_turma']);
        $qtdAlunos = (int)$_POST['qtd_alunos'];
        ?>
        <h2>Turma: <?php echo $nomeTurma; ?></h2>
        <form method="post" action="">
            <input type="hidden" name="etapa" value="3">
            <input type="hidden" name="nome_turma" value="<?php echo $nomeTurma; ?>">
            <input type="hidden" name="qtd_alunos" value="<?php echo $qtdAlunos; ?>">

            <?php for ($i = 0; $i < $qtdAlunos; $i++): ?>
                <fieldset style="margin-bottom: 10px;">
                    <legend>Aluno <?php echo $i + 1; ?></legend>
                    <label>Nome:</label>
                    <input type="text" name="nome[]" required><br>
                    <label>Nota Prova 1:</label>
                    <input type="number" name="nota1[]" step="0.1" min="0" max="10" required><br>
                    <label>Nota Prova 2:</label>
                    <input type="number" name="nota2[]" step="0.1" min="0" max="10" required><br>
                    <label>Nota Trabalho:</label>
                    <input type="number" name="trabalho[]" step="0.1" min="0" max="10" required>
                </fieldset>
            <?php endfor; ?>
            <button type="submit">Calcular e Gerar Relatório</button>
        </form>
        <p><a href="?">Voltar ao início</a></p>
    <?php elseif ($etapa === 3): ?>
        <!-- Etapa 3: Processamento e relatório -->
        <?php
        $nomeTurma = htmlspecialchars($_POST['nome_turma']);
        $qtdAlunos = (int)$_POST['qtd_alunos'];

        // Recupera arrays
        $nomes = $_POST['nome'];
        $notas1 = $_POST['nota1'];
        $notas2 = $_POST['nota2'];
        $trabalhos = $_POST['trabalho'];

        // Arrays para armazenar resultados
        $medias = [];
        $raizes = [];
        $difs = [];
        $situacoes = [];
        $dadosAlunos = []; // Para tabela

        // Processamento de cada aluno
        for ($i = 0; $i < $qtdAlunos; $i++) {
            $n1 = (float) $notas1[$i];
            $n2 = (float) $notas2[$i];
            $trab = (float) $trabalhos[$i];
            $media = calcularMedia($n1, $n2, $trab);
            $raiz = raizSomaNotas($n1, $n2, $trab);
            $dif = diferencaAbsoluta($n1, $n2, $trab);
            $sit = situacaoAluno($media);

            $medias[] = $media;
            $raizes[] = $raiz;
            $difs[] = $dif;
            $situacoes[] = $sit;

            $dadosAlunos[] = [
                'nome' => htmlspecialchars($nomes[$i]),
                'nota1' => $n1,
                'nota2' => $n2,
                'trabalho' => $trab,
                'media' => $media,
                'raizSoma' => $raiz,
                'diferenca' => $dif,
                'situacao' => $sit
            ];
        }

        // Estatísticas da turma
        $mediaGeral = array_sum($medias) / $qtdAlunos;
        $maiorMedia = max($medias);
        $menorMedia = min($medias);

        $qtdAprovados = count(array_filter($situacoes, fn($s) => $s === 'Aprovado'));
        $qtdRecuperacao = count(array_filter($situacoes, fn($s) => $s === 'Recuperação'));
        $qtdReprovados = count(array_filter($situacoes, fn($s) => $s === 'Reprovado'));

        $percentualAprovacao = ($qtdAprovados / $qtdAlunos) * 100;

        // Soma total de todas as notas lançadas
        $somaTotalNotas = 0;
        for ($i = 0; $i < $qtdAlunos; $i++) {
            $somaTotalNotas += $notas1[$i] + $notas2[$i] + $trabalhos[$i];
        }

        // Mensagem automática de desempenho geral
        if ($percentualAprovacao >= 70) {
            $classeMsg = 'sucesso';
            $textoMsg = "Parabéns! A turma <strong>$nomeTurma</strong> obteve um ótimo desempenho com {$percentualAprovacao}% de aprovação.";
        } elseif ($percentualAprovacao >= 50) {
            $classeMsg = 'alerta';
            $textoMsg = "A turma <strong>$nomeTurma</strong> está em situação intermediária. {$percentualAprovacao}% de aprovação. Reforce os estudos dos alunos em recuperação.";
        } else {
            $classeMsg = 'erro';
            $textoMsg = "Atenção! A turma <strong>$nomeTurma</strong> apresenta baixo rendimento, apenas {$percentualAprovacao}% de aprovação. Intervenção pedagógica urgente.";
        }
        ?>

        <h2>Relatório Completo - Turma: <?php echo $nomeTurma; ?></h2>

        <!-- Tabela de alunos -->
        <table>
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Prova 1</th>
                    <th>Prova 2</th>
                    <th>Trabalho</th>
                    <th>Média</th>
                    <th>√Soma Notas</th>
                    <th>Dif. Maior/Menor</th>
                    <th>Situação</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($dadosAlunos as $aluno): ?>
                <tr>
                    <td><?php echo $aluno['nome']; ?></td>
                    <td><?php echo number_format($aluno['nota1'], 1); ?></td>
                    <td><?php echo number_format($aluno['nota2'], 1); ?></td>
                    <td><?php echo number_format($aluno['trabalho'], 1); ?></td>
                    <td><?php echo number_format($aluno['media'], 2); ?></td>
                    <td><?php echo number_format($aluno['raizSoma'], 2); ?></td>
                    <td><?php echo number_format($aluno['diferenca'], 2); ?></td>
                    <td class="<?php echo strtolower(str_replace('ç','c', $aluno['situacao'])); // classe CSS ?>">
                        <?php echo $aluno['situacao']; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Resumo estatístico -->
        <h3>Estatísticas da Turma</h3>
        <ul>
            <li><strong>Média geral da turma:</strong> <?php echo number_format($mediaGeral, 2); ?></li>
            <li><strong>Maior média:</strong> <?php echo number_format($maiorMedia, 2); ?></li>
            <li><strong>Menor média:</strong> <?php echo number_format($menorMedia, 2); ?></li>
            <li><strong>Aprovados:</strong> <?php echo $qtdAprovados; ?></li>
            <li><strong>Em recuperação:</strong> <?php echo $qtdRecuperacao; ?></li>
            <li><strong>Reprovados:</strong> <?php echo $qtdReprovados; ?></li>
            <li><strong>Percentual de aprovação:</strong> <?php echo number_format($percentualAprovacao, 1); ?>%</li>
            <li><strong>Soma total de todas as notas lançadas:</strong> <?php echo number_format($somaTotalNotas, 1); ?></li>
        </ul>

        <!-- Mensagem automática -->
        <div class="mensagem <?php echo $classeMsg; ?>">
            <?php echo $textoMsg; ?>
        </div>

        <p><a href="?">Nova análise</a></p>
    <?php endif; ?>
</div>
</body>
</html>