<?php
$pageTitle = 'Home';
include 'includes/header.php';
?>

<div class="pt-10 pb-12">
    <h1 class="text-4xl font-extrabold text-zinc-100 tracking-tight mb-2">Espi's Tools</h1>
    <p class="text-zinc-500 text-base max-w-lg">A small collection of randomization utilities.</p>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-3">

    <?php foreach ($PAGES as $file => $page): ?>
        <?php if ($file === 'index.php') continue; ?>
        <a href="/<?= $file ?>" class="group block rounded-xl p-5 border border-zinc-800 hover:border-zinc-700 hover:bg-zinc-900/50 transition-all bg-zinc-925">
            <h2 class="text-base font-semibold text-zinc-100 mb-1 group-hover:text-red-400 transition-colors"><?= $page['title'] ?></h2>
            <p class="text-zinc-500 text-sm leading-relaxed"><?= $page['description'] ?></p>
        </a>
    <?php endforeach; ?>

</div>

<?php include 'includes/footer.php'; ?>
