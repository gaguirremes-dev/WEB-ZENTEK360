<?php
/** Espera $p (fila de tienda_listar_productos) y $pdo en scope. */
$precioVitrina = tienda_precio_vitrina($pdo, ['precio_neto' => $p['precio_neto'], 'moneda' => $p['moneda']]);
$agotado = ($p['estado'] === 'agotado');
$bajoPedido = ($p['disponibilidad'] === 'bajo_pedido');
?>
<div class="producto-card">
  <a href="/producto/<?= urlencode($p['slug']) ?>" class="producto-card-link">
    <?php if ($p['imagen']): ?>
      <img class="producto-img" src="/images/tienda-productos/<?= htmlspecialchars($p['imagen']) ?>" alt="<?= htmlspecialchars($p['nombre']) ?>" loading="lazy" decoding="async">
    <?php else: ?>
      <div class="producto-img"></div>
    <?php endif; ?>
  </a>
  <div class="p-5 flex flex-col flex-1">
    <?php if ($p['marca_nombre']): ?>
      <span class="text-[10px] font-bold text-secondary uppercase tracking-wide mb-1"><?= htmlspecialchars($p['marca_nombre']) ?></span>
    <?php endif; ?>
    <h3 class="text-[15px] font-bold text-primary mb-1">
      <a href="/producto/<?= urlencode($p['slug']) ?>" class="no-underline text-primary hover:text-secondary"><?= htmlspecialchars($p['nombre']) ?></a>
    </h3>
    <?php if ($p['descripcion_corta']): ?>
      <p class="text-[12.5px] text-primary/55 leading-relaxed mb-3"><?= htmlspecialchars($p['descripcion_corta']) ?></p>
    <?php endif; ?>
    <div class="mt-auto">
      <div class="flex items-center justify-between mb-3">
        <span class="text-[18px] font-black text-primary">S/ <?= number_format($precioVitrina, 2) ?></span>
        <?php if ($agotado): ?>
          <span class="stock-badge stock-off">Agotado</span>
        <?php elseif ($bajoPedido): ?>
          <span class="stock-badge stock-warn">Bajo pedido · <?= (int)$p['dias_entrega'] ?> días</span>
        <?php else: ?>
          <span class="stock-badge stock-ok">Stock disponible</span>
        <?php endif; ?>
      </div>
      <a href="<?= htmlspecialchars(tienda_whatsapp_link($p['nombre'], $p['sku'])) ?>" target="_blank" rel="noopener noreferrer"
         class="w-full inline-flex items-center justify-center gap-2 bg-secondary hover:bg-[#d94d0b] text-white text-[13px] font-semibold px-4 py-[10px] rounded-lg no-underline transition-colors duration-200">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M17.5 14.4c-.3-.1-1.7-.8-2-1-.3-.1-.5-.1-.7.1-.2.3-.8 1-.9 1.2-.2.2-.3.2-.6.1-.3-.1-1.3-.5-2.4-1.5-.9-.8-1.5-1.8-1.7-2.1-.2-.3 0-.5.1-.6.1-.1.3-.3.4-.5.1-.1.2-.3.3-.4.1-.2 0-.4 0-.5-.1-.1-.7-1.7-1-2.3-.2-.6-.5-.5-.7-.5h-.6c-.2 0-.5.1-.8.4-.3.3-1 1-1 2.4s1.1 2.8 1.2 3c.1.2 2.2 3.4 5.3 4.7.7.3 1.3.5 1.8.6.7.2 1.4.2 1.9.1.6-.1 1.7-.7 2-1.4.2-.7.2-1.2.2-1.4-.1-.1-.3-.2-.6-.3z"/><path d="M12 2a10 10 0 00-8.6 15L2 22l5.1-1.3A10 10 0 1012 2zm0 18.2a8.2 8.2 0 01-4.2-1.2l-.3-.2-3 .8.8-2.9-.2-.3A8.2 8.2 0 1112 20.2z"/></svg>
        Cotizar por WhatsApp
      </a>
    </div>
  </div>
</div>
