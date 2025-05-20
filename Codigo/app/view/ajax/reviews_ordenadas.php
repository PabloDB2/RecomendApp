<?php
require_once(__DIR__ . '/../../../rutas.php');
require_once(CONTROLLER . 'UsuarioController.php');
require_once(CONTROLLER . 'ResenaController.php');
require_once(MODEL . 'Resena.php');

header('Content-Type: application/json');
session_start();

$movie_id = intval($_GET['id'] ?? 0);
$sort = $_GET['sort'] ?? 'recientes';

$nombre_usuario = $_SESSION['nombre_usuario'] ?? null;
if (!$nombre_usuario) {
    echo json_encode([
        'success' => true,
        'html' => '<div class="text-center py-4"><p class="fechaReseña">Inicia sesión para ver las reseñas</p></div>'
    ]);
    exit;
}

$usuarioController = new UsuarioController();
$usuario = $usuarioController->getUserByName($nombre_usuario);
$id_usuario = $usuario ? $usuario->getIdUsuario() : null;

$resenaController = new ResenaController();
$resenas = $resenaController->getResenasByApiId($movie_id, 'película');

if (!$resenas || !is_array($resenas)) {
    $resenas = [];
} else {
    $resenas = array_values(array_reduce($resenas, function ($carry, $r) {
        $carry[$r['id_reseña']] = $r;
        return $carry;
    }, []));
}


foreach ($resenas as &$r) {
    $r['usuarioDioLike'] = $id_usuario ? Resena::usuarioDioLike($id_usuario, $r['id_reseña']) : false;
}
unset($r);

if ($sort === 'antiguas') {
    usort($resenas, fn($a, $b) => strtotime($a['fecha']) <=> strtotime($b['fecha']));
} elseif ($sort === 'likeados') {
    usort($resenas, fn($a, $b) => $b['likes'] <=> $a['likes']);
} else {
    usort($resenas, fn($a, $b) => strtotime($b['fecha']) <=> strtotime($a['fecha']));
}

function renderReview($r, $id_usuario, $nombre_usuario)
{
    ob_start();
    $textoSeguro = htmlspecialchars(ltrim($r['texto'], "\r\n"));
?>
    <div class="review-item mb-4">
        <div class="d-flex">
            <div class="review-avatar me-3">
                <img src="../images/avatars/<?= htmlspecialchars($r['avatar']) ?>" alt="<?= htmlspecialchars($r['nombre_usuario']) ?>" class="rounded-circle" width="50" height="50">
            </div>
            <div class="review-content w-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <h5 class="mb-0"><?= htmlspecialchars($r['nombre_usuario']) ?></h5>
                        <div class="rating-display">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star <?= $i <= $r['puntuacion'] ? 'text-warning' : 'text-muted' ?>"></i>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <div class="like-button-container">
                        <?php if ($nombre_usuario): ?>
                            <button class="btn btn-outline-light btn-like-review <?= $r['usuarioDioLike'] ? 'active' : '' ?>" data-id="<?= $r['id_reseña'] ?>">
                                <i class="<?= $r['usuarioDioLike'] ? 'fas' : 'far' ?> fa-heart"></i>
                                <span class="likes-count"><?= $r['likes'] ?></span>
                            </button>
                        <?php else: ?>
                            <span class="likes-count" style="color:var(--green);font-weight:600;"><i class="far fa-heart"></i> <?= $r['likes'] ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <p class="review-text" style="overflow-wrap: break-word; word-break: break-word; white-space: pre-line; width: 100%; max-width: 100%; display: block; box-sizing: border-box; margin-bottom: 10px;"><?= $textoSeguro ?></p>
                <small class="fechaReseña"><?= date('d/m/Y H:i', strtotime($r['fecha'])) ?></small>
            </div>
        </div>
    </div>
<?php
    return ob_get_clean();
}

$htmlParts = [];
foreach ($resenas as $resena) {
    if ($nombre_usuario && $resena['id_usuario'] == $id_usuario) continue;
    $htmlParts[] = renderReview($resena, $id_usuario, $nombre_usuario);
}

$html = implode('', $htmlParts);

if (trim($html) === '') {
    $html = '<div class="text-center py-4"><p class="fechaReseña">Aún no hay reseñas para esta película. ¡Sé el primero en opinar!</p></div>';
}

echo json_encode([
    'success' => true,
    'html' => $html,
]);
