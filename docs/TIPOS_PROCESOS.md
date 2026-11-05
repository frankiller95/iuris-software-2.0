# Tipos de procesos en Iuris

> Convertido desde `docs/PROCESOS EN IURIS.docx`. Estos datos se cargan en la base de datos con el seeder
> `backend/database/seeders/TiposProcesosSeeder.php` (tablas `areas_proceso` y `tipos_proceso`).
>
> Se corrigió la ortografía de un ítem del documento original: "INTRESES DE LA LIQUIDACON" → "Intereses de la liquidación".

## Procesos de Civil
1. Declarativo verbal y declarativo verbal sumario
2. Ejecutivos
3. Sucesión
4. Liquidación de la sociedad conyugal
5. Liquidación de la sociedad patrimonial
6. Jurisdicción voluntaria
7. Responsabilidad civil extracontractual
8. Monitorio
9. Divorcio
10. Declarativo de pertenencia

## Procesos de Pensiones
1. Indemnización sustitutiva de la pensión de vejez
2. Devolución de saldos
3. Invalidez
4. Sobreviviente
5. Vejez
6. Reliquidación de mesada pensional
7. Corrección de semanas
8. Indemnización de perjuicios
9. Reliquidación de la pensión de invalidez
10. Reliquidación de la pensión de sobreviviente

## Procesos de Laboral
1. Acoso laboral
2. Reintegro laboral
3. Intereses de la liquidación
4. Intereses a las cesantías
5. Fuero sindical
6. Despido sin justa causa
7. Renuncia con justa causa
8. Descargos
9. Pago de prestaciones sociales
10. Despido colectivo

## Procesos de Familia
1. Unión marital de hecho
2. Patria potestad
3. Corrección de registros civiles
4. Testamentos
5. Capitulaciones matrimoniales
6. Fijación de cuota alimentaria

## Procesos de Penal
1. Representación de víctimas
2. Audiencia preliminar
3. Ejecución de penas
4. Legalización de captura
5. Concierto para delinquir
6. Homicidio
7. Hurto

## Modelo de datos

| Tabla | Campos |
|---|---|
| `areas_proceso` | id, nombre (Civil, Pensiones, Laboral, Familia, Penal), activo |
| `tipos_proceso` | id, area_proceso_id → areas_proceso, nombre, activo |

Cada proceso (módulo siguiente) tendrá un `tipo_proceso_id` y un `cliente_id`.
