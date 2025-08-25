# Política de Versionamento da API

## Abordagem
- A API segue **versionamento semântico** (SemVer) na forma `MAJOR.MINOR.PATCH`.
- Apenas a **versão major** aparece no **path** da URL (`/api/v1/...`).
- **Minor** e **Patch** não aparecem no path, sendo controlados por **tags no Git** e documentados em `CHANGELOG.md`.

Exemplo:
- `/api/v1/vehicles` → mesma rota para `1.0.0`, `1.1.0` e `1.2.1`.
- Apenas alterações **breaking** exigem novo path (`/api/v2/...`).

---

## API-Version Header
- **Não utilizamos** o header `API-Version`.  
- Toda negociação de versão ocorre exclusivamente pelo path.  
- Esta decisão evita ambiguidade e simplifica a integração do cliente.

---

## Compatibilidade e Depreciação
- Ao lançar `vN`, a versão `v(N-1)` será mantida em produção por **no mínimo 6 meses**.  
- Período de sunset poderá ser maior, dependendo do SLA acordado.  
- Durante o sunset:
  - Os clientes recebem **warnings via header `Deprecation`**.
  - A documentação indica prazos de migração e mudanças breaking.

---

## Regras de Major / Minor / Patch
- **Major (X.0.0)**: alterações incompatíveis (ex.: mudanças de contrato, remoção de campos obrigatórios).
- **Minor (X.Y.0)**: adição retrocompatível (novos endpoints, novos campos opcionais).
- **Patch (X.Y.Z)**: correções sem alteração de contrato (bugfixes, ajustes de performance).

---

## Erros de Versionamento
Quando o cliente solicita uma versão **não suportada**:

```json
{
  "code": "UNSUPPORTED_API_VERSION",
  "message": "Versão de API não suportada. Use /api/v1."
}
````

---

## SLA de Versionamento

* **Major N** sempre suportada.
* **Major N-1** garantida por no mínimo 6 meses após a liberação de `N`.
* **Minor/Patch** são incrementais e sempre compatíveis dentro do mesmo major.

---

## Referências

* [SemVer 2.0.0](https://semver.org/lang/pt-BR/)
* CHANGELOG.md (histórico detalhado)
* ADR-03 (padrões de API)

