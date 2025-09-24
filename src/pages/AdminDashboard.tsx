import React, { useState, useEffect } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { Plus, Edit, Trash2, Eye, LogOut, Star, Building } from 'lucide-react';
import { apiService, Property } from '../services/api';

export const AdminDashboard: React.FC = () => {
  const navigate = useNavigate();
  const [properties, setProperties] = useState<Property[]>([]);
  const [loading, setLoading] = useState(true);
  const [deleteId, setDeleteId] = useState<string | null>(null);
  const [error, setError] = useState('');
  const [currentPage, setCurrentPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);
  const [totalProperties, setTotalProperties] = useState(0);
  const itemsPerPage = 10;

  useEffect(() => {
    // Check authentication
    if (!apiService.isAuthenticated()) {
      navigate('/admin/login');
      return;
    }

    fetchProperties();
  }, [navigate]);

  const fetchProperties = async (page: number = currentPage) => {
    setLoading(true);
    try {
      const result = await apiService.getProperties({ active: 'all' }, page, itemsPerPage);
      if (result.success && result.data) {
        setProperties(result.data);
        if (result.meta) {
          setTotalPages(result.meta.pages || 1);
          setTotalProperties(result.meta.total || 0);
          setCurrentPage(result.meta.page || 1);
        }
      } else {
        setError(result.error || 'Грешка при зареждане на имотите');
      }
    } catch (error) {
      setError('Грешка при зареждане на имотите');
    } finally {
      setLoading(false);
    }
  };

  const handleDelete = async (id: string) => {
    try {
      const result = await apiService.deleteProperty(id);
      if (result.success) {
        setProperties(prev => prev.filter(p => p.id !== id));
        setDeleteId(null);
        // Refresh the current page to maintain pagination
        fetchProperties(currentPage);
      } else {
        setError(result.error || 'Грешка при изтриване');
      }
    } catch (error) {
      setError('Грешка при изтриване');
    }
  };

  const handlePageChange = (page: number) => {
    if (page >= 1 && page <= totalPages && page !== currentPage) {
      setCurrentPage(page);
      fetchProperties(page);
    }
  };

  const handleLogout = async () => {
    await apiService.logout();
    navigate('/admin/login');
  };

  const toggleFeatured = async (id: string, featured: boolean) => {
    try {
      const result = await apiService.updateProperty(id, { featured: !featured });
      if (result.success) {
        setProperties(prev => prev.map(p => 
          p.code === id ? { ...p, featured: !featured } : p
        ));
        // Trigger a refresh of featured properties on homepage
        window.dispatchEvent(new CustomEvent('featuredPropertiesChanged'));
      }
    } catch (error) {
      setError('Грешка при обновяване');
    }
  };

  const toggleActive = async (id: string, active: boolean) => {
    try {
      const result = await apiService.updateProperty(id, { active: !active });
      if (result.success) {
        setProperties(prev => prev.map(p => 
          p.code === id ? { ...p, active: !active } : p
        ));
      }
    } catch (error) {
      setError('Грешка при обновяване');
    }
  };

  if (loading) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-blue-600"></div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Header */}
      <header className="bg-white shadow-sm border-b">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex justify-between items-center py-6">
            <div className="flex items-center gap-4">
              <img 
                src="/logo.png" 
                alt="ConsultingG Logo" 
                className="w-12 h-12 rounded-xl object-contain"
              />
              <div>
                <h1 className="text-2xl font-bold text-gray-900">Admin Panel</h1>
                <p className="text-gray-600">ConsultingG Real Estate</p>
              </div>
            </div>
            
            <div className="flex items-center gap-4">
              <Link
                to="/"
                target="_blank"
                className="flex items-center gap-2 px-4 py-2 text-gray-600 hover:text-gray-900 transition-colors"
              >
                <Eye className="w-4 h-4" />
                Виж сайта
              </Link>
              <Link
                to="/admin/services"
                className="flex items-center gap-2 px-4 py-2 text-gray-600 hover:text-gray-900 transition-colors"
              >
                Услуги
              </Link>
              <Link
                to="/admin/pages"
                className="flex items-center gap-2 px-4 py-2 text-gray-600 hover:text-gray-900 transition-colors"
              >
                Страници
              </Link>
              <button
                onClick={handleLogout}
                className="flex items-center gap-2 px-4 py-2 text-red-600 hover:text-red-700 transition-colors"
              >
                <LogOut className="w-4 h-4" />
                Изход
              </button>
            </div>
          </div>
        </div>
      </header>

      {/* Main Content */}
      <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {/* Stats Cards */}
        <div className="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
          <div className="bg-white rounded-xl shadow-lg p-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm font-medium text-gray-600">Общо имоти</p>
                <p className="text-2xl font-bold text-gray-900">{properties.length}</p>
              </div>
              <Building className="w-8 h-8 text-blue-600" />
            </div>
          </div>
          
          <div className="bg-white rounded-xl shadow-lg p-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm font-medium text-gray-600">Активни</p>
                <p className="text-2xl font-bold text-green-600">
                  {properties.filter(p => p.active).length}
                </p>
              </div>
              <Eye className="w-8 h-8 text-green-600" />
            </div>
          </div>
          
          <div className="bg-white rounded-xl shadow-lg p-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm font-medium text-gray-600">Препоръчани</p>
                <p className="text-2xl font-bold text-yellow-600">
                  {properties.filter(p => p.featured).length}
                </p>
              </div>
              <Star className="w-8 h-8 text-yellow-600" />
            </div>
          </div>
          
          <div className="bg-white rounded-xl shadow-lg p-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm font-medium text-gray-600">За продажба</p>
                <p className="text-2xl font-bold text-purple-600">
                  {properties.filter(p => p.transaction_type === 'sale').length}
                </p>
              </div>
              <Building className="w-8 h-8 text-purple-600" />
            </div>
          </div>
        </div>

        {/* Properties Table */}
        <div className="bg-white rounded-xl shadow-lg overflow-hidden">
          <div className="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h2 className="text-xl font-bold text-gray-900">Управление на имоти</h2>
            <Link
              to="/admin/new"
              className="flex items-center gap-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white px-4 py-2 rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all duration-200"
            >
              <Plus className="w-4 h-4" />
              Добави имот
            </Link>
          </div>

          {error && (
            <div className="px-6 py-4 bg-red-50 border-b border-red-200">
              <p className="text-red-700">{error}</p>
            </div>
          )}

          <div className="overflow-x-auto">
            <table className="w-full">
              <thead className="bg-gray-50">
                <tr>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Имот
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Цена
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Локация
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Статус
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Действия
                  </th>
                </tr>
              </thead>
              <tbody className="bg-white divide-y divide-gray-200">
                {properties.map((property) => (
                  <tr key={property.id} className={`hover:bg-gray-50 ${
                    property.transaction_type === 'rent' 
                      ? 'bg-gradient-to-r from-sky-50/30 to-transparent border-l-4 border-sky-400' 
                      : ''
                  }`}>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <div className="flex items-center">
                        <div className="flex-shrink-0 h-12 w-12">
                          <img
                            className="h-12 w-12 rounded-lg object-cover"
                            src={property.images?.[0]?.url || property.images?.[0]?.image_url || '/images/placeholder.jpg'}
                            alt={property.title}
                            onError={(e) => {
                              const target = e.target as HTMLImageElement;
                              target.src = '/images/placeholder.jpg';
                            }}
                          />
                        </div>
                        <div className="ml-4">
                          <div className="text-sm font-medium text-gray-900 max-w-xs truncate">
                            {property.title}
                          </div>
                          <div className="text-sm text-gray-500">
                            {property.property_type} • {property.area}м²
                          </div>
                        </div>
                      </div>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <div className="text-sm font-medium text-gray-900">
                        €{Math.floor(property.price).toLocaleString()}
                      </div>
                      <div className={`text-sm font-medium flex items-center gap-1 ${
                        property.transaction_type === 'rent' 
                          ? 'text-sky-600' 
                          : 'text-gray-500'
                      }`}>
                        {property.transaction_type === 'rent' && (
                          <div className="w-2 h-2 bg-sky-500 rounded-full animate-pulse"></div>
                        )}
                        {property.transaction_type === 'sale' ? 'Продажба' : 'Под наем'}
                      </div>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <div className="text-sm text-gray-900">{property.city_region}</div>
                      <div className="text-sm text-gray-500">{property.district}</div>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <div className="flex flex-col gap-1">
                        <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${
                          property.active 
                            ? 'bg-green-100 text-green-800' 
                            : 'bg-red-100 text-red-800'
                        }`}>
                          {property.active ? 'Активен' : 'Неактивен'}
                        </span>
                        {property.featured && (
                          <span className="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                            Препоръчан
                          </span>
                        )}
                      </div>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                      <div className="flex items-center gap-2">
                        <Link
                          to={`/properties/${property.id}`}
                          target="_blank"
                          className="text-blue-600 hover:text-blue-900 p-1 rounded"
                          title="Преглед"
                        >
                          <Eye className="w-4 h-4" />
                        </Link>
                        <Link
                          to={`/admin/edit/${property.id}`}
                          className="text-indigo-600 hover:text-indigo-900 p-1 rounded"
                          title="Редактиране"
                        >
                          <Edit className="w-4 h-4" />
                        </Link>
                        <button
                          onClick={() => toggleFeatured(property.id, property.featured)}
                          className={`p-1 rounded ${
                            property.featured 
                              ? 'text-yellow-600 hover:text-yellow-900' 
                              : 'text-gray-400 hover:text-yellow-600'
                          }`}
                          title={property.featured ? 'Премахни от препоръчани' : 'Добави в препоръчани'}
                        >
                          <Star className="w-4 h-4" fill={property.featured ? 'currentColor' : 'none'} />
                        </button>
                        <button
                          onClick={() => toggleActive(property.id, property.active)}
                          className={`px-2 py-1 text-xs rounded ${
                            property.active 
                              ? 'bg-red-100 text-red-700 hover:bg-red-200' 
                              : 'bg-green-100 text-green-700 hover:bg-green-200'
                          }`}
                          title={property.active ? 'Деактивирай' : 'Активирай'}
                        >
                          {property.active ? 'Скрий' : 'Покажи'}
                        </button>
                        <button
                          onClick={() => setDeleteId(property.id)}
                          className="text-red-600 hover:text-red-900 p-1 rounded"
                          title="Изтриване"
                        >
                          <Trash2 className="w-4 h-4" />
                        </button>
                      </div>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>

          {properties.length === 0 && !loading && (
            <div className="text-center py-12">
              <Building className="w-12 h-12 text-gray-400 mx-auto mb-4" />
              <h3 className="text-lg font-medium text-gray-900 mb-2">Няма имоти</h3>
              <p className="text-gray-500 mb-4">Започнете като добавите първия имот</p>
              <Link
                to="/admin/new"
                className="inline-flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors"
              >
                <Plus className="w-4 h-4" />
                Добави имот
              </Link>
            </div>
          )}

          {/* Pagination Controls */}
          {properties.length > 0 && totalPages > 1 && (
            <div className="px-6 py-4 bg-gray-50 border-t border-gray-200">
              <div className="flex items-center justify-between">
                <div className="text-sm text-gray-700">
                  Показани <span className="font-medium">{((currentPage - 1) * itemsPerPage) + 1}</span> до{' '}
                  <span className="font-medium">{Math.min(currentPage * itemsPerPage, totalProperties)}</span> от{' '}
                  <span className="font-medium">{totalProperties}</span> имота
                </div>
                <div className="flex items-center gap-2">
                  <button
                    onClick={() => handlePageChange(currentPage - 1)}
                    disabled={currentPage === 1}
                    className={`px-3 py-2 text-sm font-medium rounded-lg transition-colors ${
                      currentPage === 1
                        ? 'text-gray-400 cursor-not-allowed'
                        : 'text-gray-700 hover:text-gray-900 hover:bg-gray-100'
                    }`}
                  >
                    Предишна
                  </button>
                  
                  <div className="flex gap-1">
                    {Array.from({ length: Math.min(totalPages, 5) }, (_, i) => {
                      let pageNumber;
                      if (totalPages <= 5) {
                        pageNumber = i + 1;
                      } else if (currentPage <= 3) {
                        pageNumber = i + 1;
                      } else if (currentPage >= totalPages - 2) {
                        pageNumber = totalPages - 4 + i;
                      } else {
                        pageNumber = currentPage - 2 + i;
                      }
                      
                      return (
                        <button
                          key={pageNumber}
                          onClick={() => handlePageChange(pageNumber)}
                          className={`px-3 py-2 text-sm font-medium rounded-lg transition-colors ${
                            pageNumber === currentPage
                              ? 'bg-blue-600 text-white'
                              : 'text-gray-700 hover:text-gray-900 hover:bg-gray-100'
                          }`}
                        >
                          {pageNumber}
                        </button>
                      );
                    })}
                  </div>
                  
                  <button
                    onClick={() => handlePageChange(currentPage + 1)}
                    disabled={currentPage === totalPages}
                    className={`px-3 py-2 text-sm font-medium rounded-lg transition-colors ${
                      currentPage === totalPages
                        ? 'text-gray-400 cursor-not-allowed'
                        : 'text-gray-700 hover:text-gray-900 hover:bg-gray-100'
                    }`}
                  >
                    Следваща
                  </button>
                </div>
              </div>
            </div>
          )}
        </div>
      </main>

      {/* Delete Confirmation Modal */}
      {deleteId && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
          <div className="bg-white rounded-xl p-6 max-w-md w-full mx-4">
            <h3 className="text-lg font-bold text-gray-900 mb-4">Потвърждение за изтриване</h3>
            <p className="text-gray-600 mb-6">
              Сигурни ли сте, че искате да изтриете този имот? Това действие не може да бъде отменено.
            </p>
            <div className="flex gap-3 justify-end">
              <button
                onClick={() => setDeleteId(null)}
                className="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors"
              >
                Отказ
              </button>
              <button
                onClick={() => handleDelete(deleteId)}
                className="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
              >
                Изтрий
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};