<section>
    <header>{{ Lang.dons-admin.list-title }}</header>
    
    <p><strong>{{ Lang.dons-admin.total-raised }}:</strong> {{ totalAmountFormatted }} €</p>
    
    <table>
        <thead>
            <tr>
                <th>{{ Lang.dons-admin.amount }}</th>
                <th>{{ Lang.dons-admin.donor }}</th>
                <th>{{ Lang.dons-admin.email }}</th>
                <th>{{ Lang.dons-admin.gateway }}</th>
                <th>{{ Lang.dons-admin.status }}</th>
                <th>{{ Lang.dons-admin.date }}</th>
            </tr>
        </thead>
        <tbody>
            {% for don in dons %}
                <tr>
                    <td><strong>{{ don.amountFormatted }} €</strong></td>
                    <td>{{ don.firstName }} {{ don.lastName }}</td>
                    <td>{{ don.email }}</td>
                    <td>{{ don.gateway }}</td>
                    <td>
                        {% if don.status == "completed" %}
                            <span class="button small success">{{ Lang.dons-admin.completed }}</span>
                        {% elseif don.status == "pending" %}
                            <span class="button small warning">{{ Lang.dons-admin.pending }}</span>
                        {% else %}
                            <span class="button small alert">{{ Lang.dons-admin.failed }}</span>
                        {% endif %}
                    </td>
                    <td>{{ don.date }}</td>
                </tr>
            {% endfor %}
        </tbody>
    </table>
</section>

