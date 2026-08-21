<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @mixin IdeHelperBaseModel
 * @property-read mixed $field_key
 * @property-read mixed $field_name
 * @property-read mixed|null $field_primary
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BaseModel filter(?array $params = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BaseModel filterBy(array|string $filters)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BaseModel filterFields(array|string $fields)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BaseModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BaseModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BaseModel query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BaseModel renamedFilterFields(array $renamedFilterFields)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BaseModel restrictedFilters(array|string $restrictedFilters)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BaseModel sort(?array $params = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BaseModel sortFields(array|string $fields)
 */
	class BaseModel extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $content_type_id
 * @property string $title
 * @property string|null $slug
 * @property string|null $content
 * @property string|null $excerpt
 * @property string $status
 * @property \Carbon\CarbonImmutable|null $published_at
 * @property int|null $author_id
 * @property string|null $featured_image
 * @property int $menu_order
 * @property array<array-key, mixed>|null $meta
 * @property array<array-key, mixed>|null $active_sections
 * @property array<array-key, mixed>|null $category_ids
 * @property array<array-key, mixed>|null $tag_ids
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \App\Models\User|null $author
 * @property-read mixed $field_key
 * @property-read mixed $field_name
 * @property-read mixed|null $field_primary
 * @property-read bool $is_published
 * @property-read \App\Models\Type|null $type
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Content filter(?array $params = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Content filterBy(array|string $filters)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Content filterFields(array|string $fields)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Content newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Content newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Content published()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Content query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Content renamedFilterFields(array $renamedFilterFields)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Content restrictedFilters(array|string $restrictedFilters)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Content sort(?array $params = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Content sortFields(array|string $fields)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Content whereActiveSections($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Content whereAuthorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Content whereCategoryIds($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Content whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Content whereContentTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Content whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Content whereExcerpt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Content whereFeaturedImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Content whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Content whereMenuOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Content whereMeta($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Content wherePublishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Content whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Content whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Content whereTagIds($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Content whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Content whereUpdatedAt($value)
 */
	class Content extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string|null $label
 * @property string|null $type
 * @property array<array-key, mixed>|null $config
 * @property array<array-key, mixed>|null $rules
 * @property bool $is_required
 * @property string|null $default_value
 * @property int $sort_order
 * @property int|null $parent_id
 * @property string|null $mode
 * @property int|null $min
 * @property int|null $max
 * @property bool|null $collapsed
 * @property bool|null $sortable
 * @property bool|null $cloneable
 * @property array<array-key, mixed>|null $layouts
 * @property int|null $type_id
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Field> $children
 * @property-read int|null $children_count
 * @property-read mixed $field_key
 * @property-read mixed $field_name
 * @property-read mixed|null $field_primary
 * @property-read \App\Models\Field|null $parent
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField filter(?array $params = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField filterBy(array|string $filters)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField filterFields(array|string $fields)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField renamedFilterFields(array $renamedFilterFields)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField restrictedFilters(array|string $restrictedFilters)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField sort(?array $params = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField sortFields(array|string $fields)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField whereCloneable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField whereCollapsed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField whereConfig($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField whereDefaultValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField whereIsRequired($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField whereLayouts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField whereMax($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField whereMin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField whereMode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField whereRules($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField whereSortable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField whereTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField whereUpdatedAt($value)
 */
	class CustomField extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $customer_id
 * @property string $customer_nama
 * @property string|null $customer_telepon
 * @property string|null $customer_alamat
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read mixed $field_key
 * @property-read mixed $field_name
 * @property-read mixed|null $field_primary
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer filter(?array $params = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer filterBy(array|string $filters)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer filterFields(array|string $fields)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer renamedFilterFields(array $renamedFilterFields)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer restrictedFilters(array|string $restrictedFilters)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer sort(?array $params = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer sortFields(array|string $fields)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereCustomerAlamat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereCustomerNama($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereCustomerTelepon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereUpdatedAt($value)
 */
	class Customer extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $delivery_id
 * @property string $delivery_code
 * @property \Carbon\CarbonImmutable $delivery_tanggal
 * @property int $delivery_id_so
 * @property int|null $delivery_id_invoice
 * @property string|null $delivery_nama_penerima
 * @property string|null $delivery_alamat_tujuan
 * @property string|null $delivery_nama_driver
 * @property int|null $delivery_id_kendaraan
 * @property int|null $delivery_id_supir
 * @property string|null $delivery_plat_kendaraan
 * @property string|null $delivery_nama_kurir
 * @property string|null $delivery_catatan
 * @property string $delivery_status
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read mixed $field_key
 * @property-read mixed $field_name
 * @property-read mixed|null $field_primary
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Delivery filter(?array $params = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Delivery filterBy(array|string $filters)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Delivery filterFields(array|string $fields)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Delivery newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Delivery newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Delivery query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Delivery renamedFilterFields(array $renamedFilterFields)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Delivery restrictedFilters(array|string $restrictedFilters)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Delivery sort(?array $params = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Delivery sortFields(array|string $fields)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Delivery whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Delivery whereDeliveryAlamatTujuan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Delivery whereDeliveryCatatan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Delivery whereDeliveryCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Delivery whereDeliveryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Delivery whereDeliveryIdInvoice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Delivery whereDeliveryIdKendaraan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Delivery whereDeliveryIdSo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Delivery whereDeliveryIdSupir($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Delivery whereDeliveryNamaDriver($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Delivery whereDeliveryNamaKurir($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Delivery whereDeliveryNamaPenerima($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Delivery whereDeliveryPlatKendaraan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Delivery whereDeliveryStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Delivery whereDeliveryTanggal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Delivery whereUpdatedAt($value)
 */
	class Delivery extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string|null $label
 * @property string|null $type
 * @property array<array-key, mixed>|null $config
 * @property array<array-key, mixed>|null $rules
 * @property bool $is_required
 * @property string|null $default_value
 * @property int $sort_order
 * @property int|null $parent_id
 * @property string|null $mode
 * @property int|null $min
 * @property int|null $max
 * @property bool|null $collapsed
 * @property bool|null $sortable
 * @property bool|null $cloneable
 * @property array<array-key, mixed>|null $layouts
 * @property int|null $type_id
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Field> $children
 * @property-read int|null $children_count
 * @property-read mixed $field_key
 * @property-read mixed $field_name
 * @property-read mixed|null $field_primary
 * @property-read Field|null $parent
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Field filter(?array $params = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Field filterBy(array|string $filters)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Field filterFields(array|string $fields)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Field newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Field newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Field query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Field renamedFilterFields(array $renamedFilterFields)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Field restrictedFilters(array|string $restrictedFilters)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Field sort(?array $params = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Field sortFields(array|string $fields)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Field whereCloneable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Field whereCollapsed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Field whereConfig($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Field whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Field whereDefaultValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Field whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Field whereIsRequired($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Field whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Field whereLayouts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Field whereMax($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Field whereMin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Field whereMode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Field whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Field whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Field whereRules($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Field whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Field whereSortable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Field whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Field whereTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Field whereUpdatedAt($value)
 */
	class Field extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $filename
 * @property string $original_filename
 * @property string $mime_type
 * @property int $size
 * @property string $disk
 * @property string $path
 * @property string|null $thumbnail_path
 * @property string|null $alt
 * @property string|null $title
 * @property string|null $caption
 * @property int|null $width
 * @property int|null $height
 * @property int|null $user_id
 * @property array<array-key, mixed>|null $meta
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property \Carbon\CarbonImmutable|null $deleted_at
 * @property-read mixed $field_key
 * @property-read mixed $field_name
 * @property-read mixed|null $field_primary
 * @property-read string $human_size
 * @property-read string|null $thumbnail
 * @property-read string $url
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media filter(?array $params = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media filterBy(array|string $filters)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media filterFields(array|string $fields)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media images()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media ofType(string $type)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media renamedFilterFields(array $renamedFilterFields)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media restrictedFilters(array|string $restrictedFilters)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media sort(?array $params = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media sortFields(array|string $fields)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereAlt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereCaption($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereDisk($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereHeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereMeta($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereMimeType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereOriginalFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereThumbnailPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereWidth($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media withoutTrashed()
 */
	class Media extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $location
 * @property array<array-key, mixed>|null $items
 * @property bool $is_active
 * @property int $sort_order
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property \Carbon\CarbonImmutable|null $deleted_at
 * @property-read mixed $field_key
 * @property-read mixed $field_name
 * @property-read mixed|null $field_primary
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu filter(?array $params = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu filterBy(array|string $filters)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu filterFields(array|string $fields)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu renamedFilterFields(array $renamedFilterFields)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu restrictedFilters(array|string $restrictedFilters)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu sort(?array $params = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu sortFields(array|string $fields)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu whereItems($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Menu withoutTrashed()
 */
	class Menu extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property string $icon
 * @property string $icon_color
 * @property string $title
 * @property string|null $body
 * @property string|null $url
 * @property string $type
 * @property bool $read
 * @property array<array-key, mixed>|null $meta
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read mixed $field_key
 * @property-read mixed $field_name
 * @property-read mixed|null $field_primary
 * @property-read \App\Models\User|null $has_user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification filter(?array $params = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification filterBy(array|string $filters)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification filterFields(array|string $fields)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification renamedFilterFields(array $renamedFilterFields)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification restrictedFilters(array|string $restrictedFilters)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification sort(?array $params = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification sortFields(array|string $fields)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereIconColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereMeta($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereRead($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereUserId($value)
 */
	class Notification extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property string|null $icon
 * @property int|null $content_type_id
 * @property array<array-key, mixed>|null $field_ids
 * @property int $sort_order
 * @property bool $is_active
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read mixed $field_key
 * @property-read mixed $field_name
 * @property-read mixed|null $field_primary
 * @property-read mixed $fields
 * @property-read \App\Models\Type|null $type
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Section filter(?array $params = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Section filterBy(array|string $filters)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Section filterFields(array|string $fields)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Section newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Section newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Section query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Section renamedFilterFields(array $renamedFilterFields)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Section restrictedFilters(array|string $restrictedFilters)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Section sort(?array $params = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Section sortFields(array|string $fields)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Section whereContentTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Section whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Section whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Section whereFieldIds($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Section whereIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Section whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Section whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Section whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Section whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Section whereUpdatedAt($value)
 */
	class Section extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string|null $slug
 * @property string $type
 * @property string|null $description
 * @property array<array-key, mixed>|null $supports
 * @property bool $is_active
 * @property int|null $menu_position
 * @property string|null $menu_icon
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Content> $contents
 * @property-read int|null $contents_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Section> $fieldGroups
 * @property-read int|null $field_groups_count
 * @property-read mixed $field_key
 * @property-read mixed $field_name
 * @property-read mixed|null $field_primary
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Section> $sections
 * @property-read int|null $sections_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Type filter(?array $params = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Type filterBy(array|string $filters)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Type filterFields(array|string $fields)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Type newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Type newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Type query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Type renamedFilterFields(array $renamedFilterFields)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Type restrictedFilters(array|string $restrictedFilters)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Type sort(?array $params = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Type sortFields(array|string $fields)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Type whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Type whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Type whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Type whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Type whereMenuIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Type whereMenuPosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Type whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Type whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Type whereSupports($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Type whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Type whereUpdatedAt($value)
 */
	class Type extends \Eloquent {}
}

namespace App\Models{
/**
 * @mixin IdeHelperUser
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string|null $avatar
 * @property string|null $phone
 * @property \Carbon\CarbonImmutable|null $verified_at
 * @property string|null $user_agama
 * @property string $role
 * @property int|null $subscribe
 * @property string|null $affiliate_code
 * @property string|null $affiliate_reff
 * @property int $affiliate_discount
 * @property string|null $rekening_nama
 * @property string|null $rekening_bank
 * @property string|null $rekening_nomor
 * @property \Carbon\CarbonImmutable|null $email_verified_at
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property string|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read mixed $field_key
 * @property-read mixed $field_name
 * @property-read mixed $field_primary
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User filter(?array $params = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User filterBy(array|string $filters)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User filterFields(array|string $fields)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User renamedFilterFields(array $renamedFilterFields)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User restrictedFilters(array|string $restrictedFilters)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User sort(?array $params = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User sortFields(array|string $fields)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereAffiliateCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereAffiliateDiscount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereAffiliateReff($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereAvatar($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRekeningBank($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRekeningNama($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRekeningNomor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereSubscribe($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTwoFactorConfirmedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTwoFactorRecoveryCodes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTwoFactorSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUserAgama($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereVerifiedAt($value)
 */
	class User extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property string $code
 * @property string $channel
 * @property \Carbon\CarbonImmutable $expires_at
 * @property bool $used
 * @property string|null $created_at
 * @property-read \App\Models\User|null $has_user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VerificationCode newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VerificationCode newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VerificationCode query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VerificationCode whereChannel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VerificationCode whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VerificationCode whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VerificationCode whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VerificationCode whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VerificationCode whereUsed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VerificationCode whereUserId($value)
 */
	class VerificationCode extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string|null $tagline
 * @property string|null $description
 * @property string|null $alamat
 * @property string|null $telepon
 * @property string|null $email
 * @property string|null $logo
 * @property string|null $favicon
 * @property array<array-key, mixed>|null $colors
 * @property array<array-key, mixed>|null $social
 * @property string|null $footer_text
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read mixed $field_key
 * @property-read mixed $field_name
 * @property-read mixed|null $field_primary
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebsiteSetting filter(?array $params = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebsiteSetting filterBy(array|string $filters)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebsiteSetting filterFields(array|string $fields)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebsiteSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebsiteSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebsiteSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebsiteSetting renamedFilterFields(array $renamedFilterFields)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebsiteSetting restrictedFilters(array|string $restrictedFilters)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebsiteSetting sort(?array $params = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebsiteSetting sortFields(array|string $fields)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebsiteSetting whereAlamat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebsiteSetting whereColors($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebsiteSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebsiteSetting whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebsiteSetting whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebsiteSetting whereFavicon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebsiteSetting whereFooterText($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebsiteSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebsiteSetting whereLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebsiteSetting whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebsiteSetting whereSocial($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebsiteSetting whereTagline($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebsiteSetting whereTelepon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebsiteSetting whereUpdatedAt($value)
 */
	class WebsiteSetting extends \Eloquent {}
}

