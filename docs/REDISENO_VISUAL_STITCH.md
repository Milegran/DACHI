# Redise&ntilde;o visual DACHI

Referencia: proyecto compartido de Stitch `Optimizacion y Redise&ntilde;o Visual`.

## Direccion visual

- Sistema: Botanical Heritage.
- Color principal: verde bosque `#157145`.
- Fondo general: `#f7faf8`.
- Superficies: blanco y verdes grisaceos de bajo contraste.
- Titulares: Playfair Display.
- Texto e interfaz: Plus Jakarta Sans.
- Navegacion de escritorio: barra lateral de `260px`, compactable a `78px` con iconos visibles.
- Navegacion movil: barra superior y menu lateral desplegable.
- Tarjetas: borde tenue, radio de `16px` y sombra verde muy suave.

## Pantallas cubiertas

- Acceso, registro y recuperacion de contrasena.
- Panel de consumidor.
- Paneles de administracion, productor y logistica.
- Catalogo de productos y detalle de producto.
- Carrito, resumen y datos de transferencia.
- Seguimiento de pedidos y direccion de entrega.
- Menus de usuario, perfil, configuracion y estados vacios.

## Catalogo integrado

- El catalogo del consumidor se presenta dentro de `Inicio` con nueve productos por pagina y paginacion numerica.
- La base de prueba contiene 27 productos activos distribuidos en tres paginas.
- Solo 15 productos tienen una resena de prueba; los restantes conservan el estado sin resenas.
- La busqueda superior filtra por nombre de producto o productor.
- Cada tarjeta muestra nombre, calificacion, stock, precio y accion de carrito.
- El detalle se abre como panel lateral y presenta procedencia, descripcion y resenas.
- La imagen principal del detalle conserva una proporcion cuadrada `1:1` en escritorio y movil.
- El detalle semicompleto incluye una galeria de hasta cinco vistas, calificacion del producto y perfil enriquecido del productor.
- La ficha del productor muestra ubicacion, informacion general, calificacion independiente y reseñas.
- La procedencia del producto incluye lugar exacto de cultivo, condiciones y proceso de cosecha o transformacion.
- `public/catalogo.php` conserva compatibilidad y redirige al bloque `#productos`.

## Ofertas

- Inicio incluye cinco ofertas de temporada debajo de la paginacion del catalogo.
- El carrusel avanza automaticamente de derecha a izquierda y mantiene controles anterior/siguiente.
- La animacion se pausa mientras el usuario interactua y se desactiva cuando el sistema solicita movimiento reducido.
- Los precios promocionales son los mismos precios vigentes usados por el carrito; el precio tachado funciona como referencia anterior.

## Navegacion adaptable

- El logo principal se muestra ampliado en la cabecera del menu lateral abierto.
- Al compactar el menu en escritorio, las etiquetas se ocultan, los iconos permanecen visibles y el logo pasa a la barra superior.
- La preferencia abierto/compacto se conserva al navegar entre Inicio, Carrito y Mis pedidos.
- En movil el menu se oculta fuera del lienzo y se abre completo sobre una capa de fondo.

## Activos de producto

- `img/products/tomate.jpg`: [Tomatoes.jpg](https://commons.wikimedia.org/wiki/File:Tomatoes.jpg), licencia CC0.
- `img/products/lechuga.jpg`: [Raw lettuce.jpg](https://commons.wikimedia.org/wiki/File:Raw_lettuce.jpg), licencia CC0.
- `img/products/miel.jpg`: [fotografia de Ionela Mat](https://unsplash.com/photos/IvDlKrxgMRk), licencia Unsplash.
- `img/products/pina.jpg`: [fotografia de pineapple](https://images.unsplash.com/photo-1550258987-190a2d41a8ba), licencia Unsplash.
- `img/products/cafe.jpg`: [fotografia de cafe](https://images.unsplash.com/photo-1447933601403-0c6688de566e), licencia Unsplash.
- `img/products/cacao.jpg`: [fotografia de chocolate](https://images.unsplash.com/photo-1511381939415-e44015466834), licencia Unsplash.
- `img/products/platano.jpg`: [fotografia de bananas](https://images.unsplash.com/photo-1603833665858-e61d17a86224), licencia Unsplash.
- `img/products/aguacate.jpg`: [fotografia de aguacate](https://images.unsplash.com/photo-1523049673857-eb18f1d7b578), licencia Unsplash.

## Regla de continuidad

Las pantallas nuevas deben reutilizar `css/dachi-botanical.css`, mantener los mismos tokens y conservar la estructura de navegacion correspondiente al ancho de pantalla. Los cambios visuales no deben renombrar identificadores usados por PHP o JavaScript ni modificar contratos de datos.
