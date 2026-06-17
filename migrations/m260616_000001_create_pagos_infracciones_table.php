<?php

use yii\db\Migration;

class m260616_000001_create_pagos_infracciones_table extends Migration
{
    public function up()
    {
        $this->createTable('pagos_infracciones', [
            'infraccion_id' => 'SERIAL PRIMARY KEY',
            'usua_id' => 'integer',
            'contri_id' => 'integer',
            'codigo_usuario' => 'varchar(64) NOT NULL',
            'codigo_contribuyente' => 'varchar(64) NOT NULL',
            'numero_documento' => 'varchar(20) NOT NULL',
            'tipo_documento' => "varchar(2) NOT NULL DEFAULT 'CI'",
            'expedido' => 'varchar(20)',
            'tipo_infraccion' => 'varchar(100) NOT NULL',
            'descripcion_infraccion' => 'text',
            'lugar_infraccion' => 'varchar(250)',
            'fecha_infraccion' => 'date',
            'gestion' => 'varchar(10)',
            'codigo_clasificador' => 'varchar(64) NOT NULL',
            'monto' => 'numeric(12,2) NOT NULL',
            'observacion' => 'text NOT NULL',
            'numero_tasa' => 'varchar(64) NOT NULL',
            'infraccion_estado' => 'smallint NOT NULL DEFAULT 1',
            'infraccion_pagado' => 'smallint NOT NULL DEFAULT 0',
            'infraccion_anulado' => 'smallint NOT NULL DEFAULT 0',
            'fecha_pago' => 'timestamp',
            'pago_ruat_payload' => 'text',
            'registro_ruat_payload' => 'text',
            'anulado_motivo' => 'varchar(250)',
            'anulado_observacion' => 'text',
            'anulado_fecha_hora' => 'timestamp',
            'created_at' => 'timestamp NOT NULL',
            'updated_at' => 'timestamp NOT NULL',
        ]);

        $this->createIndex('idx-pagos_infracciones-numero_tasa', 'pagos_infracciones', 'numero_tasa', true);
        $this->createIndex('idx-pagos_infracciones-numero_documento', 'pagos_infracciones', 'numero_documento');
        $this->createIndex('idx-pagos_infracciones-codigo_contribuyente', 'pagos_infracciones', 'codigo_contribuyente');
        $this->createIndex('idx-pagos_infracciones-tipo_infraccion', 'pagos_infracciones', 'tipo_infraccion');

        $this->addForeignKey(
            'fk-pagos_infracciones-contri_id',
            'pagos_infracciones',
            'contri_id',
            'contribuyentes',
            'contri_id',
            'SET NULL',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-pagos_infracciones-usua_id',
            'pagos_infracciones',
            'usua_id',
            'usuario',
            'usua_id',
            'SET NULL',
            'CASCADE'
        );
    }

    public function down()
    {
        $this->dropForeignKey('fk-pagos_infracciones-usua_id', 'pagos_infracciones');
        $this->dropForeignKey('fk-pagos_infracciones-contri_id', 'pagos_infracciones');
        $this->dropTable('pagos_infracciones');
    }
}
