import React, { useState, useEffect } from 'react';
import { supabase } from '../lib/supabaseClient';
import { supabaseService } from '../services/supabaseService';
import { Database, CheckCircle, XCircle, Loader } from 'lucide-react';

export const SupabaseTest: React.FC = () => {
  const [connectionStatus, setConnectionStatus] = useState<'loading' | 'success' | 'error'>('loading');
  const [properties, setProperties] = useState<any[]>([]);
  const [error, setError] = useState<string>('');

  useEffect(() => {
    testConnection();
  }, []);

  const testConnection = async () => {
    try {
      setConnectionStatus('loading');
      
      // Test basic connection
      const { data, error } = await supabase
        .from('properties')
        .select('id, title, price, city_region')
        .limit(5);

      if (error) {
        throw error;
      }

      setProperties(data || []);
      setConnectionStatus('success');
      
      // Test service layer
      const serviceResult = await supabaseService.getProperties({}, 1, 3);
      console.log('Service test result:', serviceResult);
      
    } catch (err: any) {
      console.error('Supabase connection test failed:', err);
      setError(err.message || 'Connection failed');
      setConnectionStatus('error');
    }
  };

  return (
    <div className="max-w-4xl mx-auto p-6">
      <div className="bg-white rounded-xl shadow-lg p-8">
        <div className="flex items-center gap-3 mb-6">
          <Database className="w-8 h-8 text-blue-600" />
          <h2 className="text-2xl font-bold text-gray-900">Supabase Connection Test</h2>
        </div>

        {/* Connection Status */}
        <div className="mb-6">
          <div className="flex items-center gap-3 mb-2">
            {connectionStatus === 'loading' && (
              <>
                <Loader className="w-5 h-5 text-blue-600 animate-spin" />
                <span className="text-blue-600 font-medium">Testing connection...</span>
              </>
            )}
            {connectionStatus === 'success' && (
              <>
                <CheckCircle className="w-5 h-5 text-green-600" />
                <span className="text-green-600 font-medium">Connected successfully!</span>
              </>
            )}
            {connectionStatus === 'error' && (
              <>
                <XCircle className="w-5 h-5 text-red-600" />
                <span className="text-red-600 font-medium">Connection failed</span>
              </>
            )}
          </div>
          
          {error && (
            <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
              <strong>Error:</strong> {error}
            </div>
          )}
        </div>

        {/* Connection Details */}
        <div className="bg-gray-50 rounded-lg p-4 mb-6">
          <h3 className="font-bold text-gray-900 mb-3">Connection Details</h3>
          <div className="space-y-2 text-sm">
            <div><strong>URL:</strong> {import.meta.env.VITE_SUPABASE_URL}</div>
            <div><strong>API Key:</strong> {import.meta.env.VITE_SUPABASE_ANON_KEY?.substring(0, 20)}...</div>
            <div><strong>Database:</strong> PostgreSQL</div>
          </div>
        </div>

        {/* Sample Data */}
        {properties.length > 0 && (
          <div>
            <h3 className="font-bold text-gray-900 mb-4">Sample Properties ({properties.length})</h3>
            <div className="space-y-3">
              {properties.map((property) => (
                <div key={property.id} className="bg-blue-50 border border-blue-200 rounded-lg p-4">
                  <div className="flex justify-between items-start">
                    <div>
                      <h4 className="font-medium text-gray-900">{property.title}</h4>
                      <p className="text-sm text-gray-600">{property.city_region}</p>
                    </div>
                    <div className="text-right">
                      <div className="font-bold text-blue-600">€{Math.floor(property.price || 0).toLocaleString()}</div>
                      <div className="text-xs text-gray-500">{property.id}</div>
                    </div>
                  </div>
                </div>
              ))}
            </div>
          </div>
        )}

        {/* Test Actions */}
        <div className="mt-6 pt-6 border-t border-gray-200">
          <button
            onClick={testConnection}
            className="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
          >
            Test Connection Again
          </button>
        </div>
      </div>
    </div>
  );
};