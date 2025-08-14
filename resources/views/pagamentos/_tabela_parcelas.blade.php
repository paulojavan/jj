<div class="overflow-x-auto">
    <table class="min-w-full bg-white">
        <thead class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
            <tr>
                <th class="py-3 px-6 text-left"></th>
                <th class="py-3 px-6 text-left">Ticket</th>
                <th class="py-3 px-6 text-left">Nº Parcela</th>
                <th class="py-3 px-6 text-left">Vencimento</th>
                <th class="py-3 px-6 text-right">Valor Original</th>
                <th class="py-3 px-6 text-right">Dias Atraso</th>
                <th class="py-3 px-6 text-right">Valor a Pagar</th>

            </tr>
        </thead>
        <tbody class="text-gray-600 text-sm font-light">
            @forelse ($parcelas as $parcela)
                <tr class="border-b border-gray-200 hover:bg-gray-100">
                    <td class="py-3 px-6 text-left whitespace-nowrap">
                        <input type="checkbox" name="parcelas[]" value="{{ $parcela->id_parcelas }}" class="parcela-checkbox" data-valor="{{ $parcela->valor_a_pagar }}">
                    </td>
                    <td class="py-3 px-6 text-left">{{ $parcela->ticket }}</td>
                    <td class="py-3 px-6 text-left">{{ $parcela->numero }}</td>
                    <td class="py-3 px-6 text-left">{{ \Carbon\Carbon::parse($parcela->data_vencimento)->format('d/m/Y') }}</td>
                    <td class="py-3 px-6 text-right">R$ {{ number_format($parcela->valor_parcela, 2, ',', '.') }}</td>
                    <td class="py-3 px-6 text-right text-red-500 font-semibold">{{ $parcela->dias_atraso }}</td>
                    <td class="py-3 px-6 text-right font-bold">R$ {{ number_format($parcela->valor_a_pagar, 2, ',', '.') }}</td>

                </tr>
            @empty
                <tr>
                    <td colspan="7" class="py-3 px-6 text-center">Nenhuma parcela aguardando pagamento.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
