<!-- Display Product List Before Purchase Button -->
<div id="cartTable">
    <table class="w-full mt-4">
        <tr class="bg-gray-100 text-gray-600 text-sm">
            <th class="p-2 text-start">No</th>
            <th class="p-2 text-start">Product</th>
            <th class="p-2 text-start">Quantity</th>
            <th class="p-2 text-start">Price</th>
            <th class="p-2 text-start">Action</th>
        </tr>
        <?php if (!empty($_SESSION['cart'])): ?>
            <?php $count = 1; ?>
            <?php foreach ($_SESSION['cart'] as $index => $item): ?>
                <tr>
                    <td class="p-2"><?= $count ?></td>
                    <td class="p-2"><?= htmlspecialchars($item['productTitle']) ?></td>
                    <td class="p-2 text-center">
                        <input type="number" min="1" value="<?= htmlspecialchars($item['quantity']) ?>"
                            class="cart-quantity w-16 text-center border rounded"
                            data-index="<?= $index ?>">
                    </td>
                    <td class="p-2">$<?= number_format($item['totalPrice'], 2) ?></td>
                    <td class="p-2 text-center">
                        <button type="button" class="remove-item text-red-500" data-index="<?= $index ?>">Remove</button>
                    </td>
                </tr>
                <?php $count++; ?>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="5" class="text-center text-base py-5 text-gray-400 p-2">No products added.</td>
            </tr>
        <?php endif; ?>
    </table>
</div>